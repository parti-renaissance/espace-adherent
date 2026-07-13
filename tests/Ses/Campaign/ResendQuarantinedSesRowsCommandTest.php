<?php

declare(strict_types=1);

namespace Tests\App\Ses\Campaign;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Mailchimp\Campaign\Audience\SegmentMemberStatusEnum;
use App\Ses\Client\SesEmail;
use App\Ses\Client\SesEmailClient;
use App\Ses\Client\SesSendOutcome;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\App\AbstractCommandTestCase;

/**
 * A send that fails on a 5xx or a network error leaves the row quarantined: AsyncAws reports every transport
 * failure the same way (one NetworkException), so we cannot tell whether SES accepted the mail. Nothing is ever
 * resent automatically.
 *
 * This command hands the call to a human, for the rows no SES event ever confirmed — the ones whose mail most
 * likely never left. What it must never do is resend a row an event has since proven sent: that would be the
 * very double-send the quarantine exists to prevent.
 *
 * The campaign messages route to 'sync' in the test env, so the resend really runs end to end here: the SES
 * client below is what pins WHICH recipients are actually re-sent.
 */
#[Group('functional')]
class ResendQuarantinedSesRowsCommandTest extends AbstractCommandTestCase
{
    use SesCampaignFixturesTrait;

    public function testOnlyTheUnconfirmedQuarantinedRowIsResent(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sent;
        $alreadySent = $this->createSubscribedAdherent();
        $lost = $this->createSubscribedAdherent();
        $this->addMember($campaign, $alreadySent, 1, SegmentMemberStatusEnum::Sent);
        $this->addMember($campaign, $lost, 1, SegmentMemberStatusEnum::SendErrored);
        $this->manager->flush();

        $segmentId = $campaign->getMailchimpStaticSegment()->id;
        $messageId = $campaign->getMessage()->getId();

        // Exactly one SES call, and to the quarantined recipient only: the row already Sent must never be sent a
        // second time by a repair run.
        $sesClient = $this->createMock(SesEmailClient::class);
        $sesClient
            ->expects(self::once())
            ->method('sendEmail')
            ->with(self::callback(static fn (SesEmail $email): bool => $email->to === $lost->getEmailAddress()))
            ->willReturnCallback(static fn (SesEmail $email): SesSendOutcome => SesSendOutcome::sent('ses-msg-'.$email->to))
        ;
        self::getContainer()->set(SesEmailClient::class, $sesClient);

        $tester = $this->execute($campaign->getId());

        self::assertStringContainsString('1 recipient(s) reopened', $tester->getDisplay());

        // The reopened row went back through the normal send path and closed properly, reach included.
        self::assertSame(2, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Sent));
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::SendErrored));
        self::assertSame(2, $this->countReach($messageId));
        self::assertSame(MailchimpStatusEnum::Sent, $this->reloadStatus($campaign));
    }

    public function testRowConfirmedByAnSesEventIsNeverResent(): void
    {
        $campaign = $this->createCampaign();
        $campaign->status = MailchimpStatusEnum::Sent;
        // Quarantined on an ambiguous failure — but a bounce event later proved SES DID send it. Resending would
        // deliver the same mail twice.
        $confirmed = $this->addMember($campaign, $this->createSubscribedAdherent(), 1, SegmentMemberStatusEnum::SendErrored);
        $confirmed->bouncedAt = new \DateTimeImmutable('-1 hour');
        $this->manager->flush();

        $segmentId = $campaign->getMailchimpStaticSegment()->id;

        $sesClient = $this->createMock(SesEmailClient::class);
        $sesClient->expects(self::never())->method('sendEmail');
        self::getContainer()->set(SesEmailClient::class, $sesClient);

        $tester = $this->execute($campaign->getId());

        self::assertStringContainsString('nothing to resend', $tester->getDisplay());
        self::assertSame(1, $this->countByStatus($segmentId, SegmentMemberStatusEnum::SendErrored), 'a row proven sent stays quarantined');
        self::assertSame(0, $this->countByStatus($segmentId, SegmentMemberStatusEnum::Added));
        self::assertSame(MailchimpStatusEnum::Sent, $this->reloadStatus($campaign), 'a campaign with nothing to resend is left closed');
    }

    protected function tearDown(): void
    {
        $this->seq = 0;

        parent::tearDown();
    }

    private function execute(int $campaignId): CommandTester
    {
        $tester = $this->runCommand('ses:campaign:resend-quarantined', ['campaign-id' => $campaignId, '--force' => true]);
        $tester->assertCommandIsSuccessful();

        $this->manager->clear();

        return $tester;
    }
}

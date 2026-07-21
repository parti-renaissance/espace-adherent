<?php

declare(strict_types=1);

namespace Tests\App\Mailchimp\Campaign\Audience;

use App\AdherentMessage\AdherentMessageManager;
use App\AdherentMessage\MailchimpStatusEnum;
use App\Controller\Api\AdherentMessage\SendAdherentMessageController;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Audience\AudienceMessagePreparer;
use App\Mailchimp\Campaign\Audience\BlockReasonEnum;
use App\Mailchimp\Campaign\Audience\PreparationStatusEnum;
use App\Mailchimp\Campaign\Audience\PrepareResult;
use App\Mailchimp\Campaign\Audience\SendStatusFactory;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Lock\LockFactory;
use Tests\App\AbstractKernelTestCase;
use Tests\App\Ses\Campaign\SesCampaignFixturesTrait;

/**
 * Functional proof that a publication whose preparation failed can be sent again, against a real DB.
 *
 * A publication is marked sent the instant its author clicks: the flag means "the author committed to
 * sending" and drives the timeline indexing and the push, but says nothing about whether the email left —
 * preparation is asynchronous and can still fail afterwards. On 2026-07-16 the endpoint gated the replay
 * on that flag, so a campaign blocked at finalize could never be retried and a brand new message had to
 * be built by hand, in the middle of the incident. The campaign carries the send truth; the flag does not.
 */
#[Group('functional')]
class ResendBlockedPublicationTest extends AbstractKernelTestCase
{
    use SesCampaignFixturesTrait;

    /**
     * The incident state: message marked sent, campaign blocked with PreparationErrors, nothing ever sent.
     * The endpoint must accept the replay and re-run the preparation.
     */
    public function testPublicationWithFailedPreparationCanBeSentAgain(): void
    {
        $campaign = $this->createSentMessageWithCampaign(MailchimpStatusEnum::Save);
        $campaign->markAsFailed(BlockReasonEnum::PreparationErrors);
        $this->manager->flush();

        $prepared = false;
        $response = $this->invokeEndpoint($campaign, $prepared, expectPrepare: true);

        self::assertSame(200, $response->getStatusCode());
        self::assertTrue($prepared, 'a blocked preparation must be re-run, not refused');
        self::assertTrue($campaign->getMessage()->isSent(), 'isSent keeps its meaning: the author committed');
    }

    /**
     * The replay guard did not disappear, it moved: a campaign Mailchimp already sent must be refused.
     */
    public function testPublicationWhoseCampaignAlreadyWentOutIsRefused(): void
    {
        $campaign = $this->createSentMessageWithCampaign(MailchimpStatusEnum::Sent);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('This message has been already sent.');

        $prepared = false;
        $this->invokeEndpoint($campaign, $prepared, expectPrepare: false);
    }

    /**
     * A send in flight must not be doubled either.
     */
    public function testPublicationWhoseCampaignIsSendingIsRefused(): void
    {
        $campaign = $this->createSentMessageWithCampaign(MailchimpStatusEnum::Sending);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('This message has been already sent.');

        $prepared = false;
        $this->invokeEndpoint($campaign, $prepared, expectPrepare: false);
    }

    private function invokeEndpoint(
        MailchimpCampaign $campaign,
        bool &$prepared,
        bool $expectPrepare,
    ): \Symfony\Component\HttpFoundation\Response {
        $message = $campaign->getMessage();

        // Real controller, real entity relation hydrated from the DB. Only the two collaborators that
        // would reach outside are doubled: the preparer (which would fan out the audience) and the
        // manager (which would fire the push). A refused send must reach neither.
        $preparer = $this->createMock(AudienceMessagePreparer::class);
        $preparer
            ->expects($expectPrepare ? self::once() : self::never())
            ->method('prepare')
            ->with($message, $message->getAuthor())
            ->willReturnCallback(static function () use (&$prepared): PrepareResult {
                $prepared = true;

                return PrepareResult::preparing(['preparation_status' => 'preparing']);
            })
        ;

        $manager = $this->createMock(AdherentMessageManager::class);
        $manager->expects($expectPrepare ? self::once() : self::never())->method('sendPublication')->with($message);

        $controller = new SendAdherentMessageController();

        return $controller->__invoke(
            $manager,
            $preparer,
            self::getContainer()->get(SendStatusFactory::class),
            self::getContainer()->get(LockFactory::class),
            $message,
            $message->getAuthor(),
        );
    }

    private function createSentMessageWithCampaign(MailchimpStatusEnum $status): MailchimpCampaign
    {
        $campaign = $this->createCampaign();
        $campaign->status = $status;
        $campaign->setStaticSegmentId(4242);

        $message = $campaign->getMessage();
        // isSynchronized() is derived, not stored: a publication is ready once it owns a campaign and
        // carries a subject and content. The trait sets the latter two; wire the inverse side for the first.
        $message->addMailchimpCampaign($campaign);
        // The click marked it sent before the asynchronous preparation even started.
        $message->markAsSent();

        $this->manager->flush();
        // Prove the guard reads what the DB really holds, not an in-memory leftover.
        $this->manager->refresh($campaign);

        self::assertTrue($message->isSynchronized());
        self::assertSame(PreparationStatusEnum::NotStarted, $campaign->getPreparationStatus());

        return $campaign;
    }
}

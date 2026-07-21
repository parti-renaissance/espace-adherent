<?php

declare(strict_types=1);

namespace Tests\App\Mailchimp\Campaign;

use App\AdherentMessage\MailchimpStatusEnum;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\JeMengage\Push\Command\AdherentMessageSentNotificationCommand;
use App\Mailchimp\Campaign\Audience\BlockReasonEnum;
use App\Mailchimp\Campaign\Command\SendMailchimpCampaignCommand;
use App\Mailchimp\Driver;
use App\Messenger\MessageRecorder\MessageRecorderInterface;
use App\Ses\Campaign\Message\TriggerSesCampaignMessage;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tests\App\AbstractCommandTestCase;
use Tests\App\MessengerTestTrait;
use Tests\App\Ses\Campaign\SesCampaignFixturesTrait;

/**
 * The manual escape hatch, exercised through the real console Application and the real bus.
 *
 * On 2026-07-16 this command was the documented way out of a campaign whose preparation had blocked —
 * and it could not work. It aborted on AdherentMessage::isSent(), which a publication carries from the
 * instant its author clicks, so it refused to run in the exact situation it exists for. Worse, had it
 * run, it called AdherentMessageManager::send(), whose only sender matching a publication is PushSender:
 * it would have fired a push notification at the whole audience and marked the message sent, condemning
 * the real send for good. It never reached Manager::sendMailchimpCampaign(), the sole route to
 * POST /campaigns/{id}/actions/send. The campaign had to be sent by hand against the Mailchimp API.
 */
#[Group('functional')]
class MailchimpCampaignForceSendCommandTest extends AbstractCommandTestCase
{
    use MessengerTestTrait;
    use SesCampaignFixturesTrait;

    /**
     * The incident, made recoverable: a publication marked sent, whose preparation blocked, must reach
     * the real Mailchimp send path — and must NOT fire the push that used to stand in for it.
     */
    public function testBlockedMailchimpCampaignIsDispatchedToTheRealSendPath(): void
    {
        $campaign = $this->createBlockedCampaign(sendViaMailchimp: true);

        $tester = $this->forceSend($campaign, confirm: true);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertMessageIsDispatched(SendMailchimpCampaignCommand::class, help: 'force-send must reach the real Mailchimp send command');
        $this->assertMessageIsNotDispatched(AdherentMessageSentNotificationCommand::class);
    }

    /**
     * A campaign routed to SES must take the SES trigger, mirroring the auto-send branch rather than
     * assuming every campaign is a Mailchimp one.
     */
    public function testBlockedSesCampaignIsDispatchedToTheSesTrigger(): void
    {
        $campaign = $this->createBlockedCampaign(sendViaMailchimp: false);

        $tester = $this->forceSend($campaign, confirm: true);

        self::assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertMessageIsDispatched(TriggerSesCampaignMessage::class);
        $this->assertMessageIsNotDispatched(SendMailchimpCampaignCommand::class);
    }

    /**
     * isSent() is no longer the gate — but a campaign that really did leave still must not be sent twice.
     */
    public function testCampaignAlreadySentIsRefused(): void
    {
        $campaign = $this->createBlockedCampaign(sendViaMailchimp: true);
        $campaign->status = MailchimpStatusEnum::Sent;
        $this->manager->flush();

        $tester = $this->forceSend($campaign, confirm: true);

        self::assertStringContainsString('already sent', $tester->getDisplay());
        $this->assertMessageIsNotDispatched(SendMailchimpCampaignCommand::class);
        $this->assertMessageIsNotDispatched(TriggerSesCampaignMessage::class);
    }

    public function testDeclinedConfirmationDispatchesNothing(): void
    {
        $campaign = $this->createBlockedCampaign(sendViaMailchimp: true);

        $tester = $this->forceSend($campaign, confirm: false);

        self::assertStringContainsString('Aborted by user', $tester->getDisplay());
        $this->assertMessageIsNotDispatched(SendMailchimpCampaignCommand::class);
    }

    protected function getMessageRecorder(): MessageRecorderInterface
    {
        return self::getContainer()->get(MessageRecorderInterface::class);
    }

    private function forceSend(MailchimpCampaign $campaign, bool $confirm): CommandTester
    {
        // SendMailchimpCampaignCommand is unrouted in the test env, so its handler runs inline. Doubling
        // the Driver keeps the real handler, guard and Manager in the loop while stopping at the HTTP edge.
        // A stub, not a mock: what this test pins is which message the command dispatches, never how the
        // downstream handler talks to Mailchimp.
        self::getContainer()->set(Driver::class, $this->createStub(Driver::class));

        $command = $this->application->find('mailchimp:campaign:force-send');
        $tester = new CommandTester($command);
        $tester->setInputs([$confirm ? 'yes' : 'no']);
        $tester->execute(['campaign-id' => $campaign->getId()]);

        return $tester;
    }

    /**
     * A campaign in the state the incident left it in: the author clicked (so the message is marked
     * sent), but the audience preparation errored out and refused to auto-send.
     */
    private function createBlockedCampaign(bool $sendViaMailchimp): MailchimpCampaign
    {
        $campaign = $this->createCampaign();
        $campaign->setExternalId('mc-external-'.bin2hex(random_bytes(4)));
        $campaign->sendViaMailchimp = $sendViaMailchimp;
        $campaign->markAsFailed(BlockReasonEnum::PreparationErrors);
        $campaign->getMessage()->markAsSent();

        $this->manager->flush();

        return $campaign;
    }
}

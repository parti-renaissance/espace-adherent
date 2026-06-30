<?php

declare(strict_types=1);

namespace Tests\App\Mailchimp\Campaign\Report;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\MailchimpCampaign;
use App\Mailchimp\Campaign\Report\Command\SyncReportCommand;
use App\Mailchimp\Campaign\Report\Handler\SyncReportCommandHandler;
use App\Messenger\MessageRecorder\MessageRecorderInterface;
use App\Scope\ScopeEnum;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;
use Tests\App\MessengerTestTrait;

#[Group('functional')]
class SyncReportCommandHandlerTest extends AbstractKernelTestCase
{
    use MessengerTestTrait;

    /**
     * A publication sent via SES has a local MailchimpCampaign without an external id: there is no
     * Mailchimp report to fetch. The handler must skip it instead of throwing InvalidCampaignIdException
     * (and must not reschedule the next sync step) — this is what the report-download cron triggers for
     * every SES send.
     */
    public function testSentSesPublicationWithoutMailchimpCampaignIdIsSkipped(): void
    {
        $author = $this->createAdherent('ses-report-author@test.dev');

        $message = new AdherentMessage(null, $author);
        $message->setSubject('Publication SES');
        $message->setContent('<p>Contenu</p>');
        $message->setInstanceScope(ScopeEnum::NATIONAL);

        // SES-sent: the campaign exists locally but was never created in Mailchimp (no externalId).
        $campaign = new MailchimpCampaign($message);
        $message->addMailchimpCampaign($campaign);
        $message->markAsSent();

        $this->manager->persist($author);
        $this->manager->persist($message);
        $this->manager->persist($campaign);
        $this->manager->flush();

        // Must NOT throw InvalidCampaignIdException (pre-fix it did), and must NOT reschedule the next
        // sync step — the early return short-circuits before the per-step processing and the reschedule.
        self::getContainer()->get(SyncReportCommandHandler::class)(new SyncReportCommand($message->getUuid()));

        self::assertFalse(
            $this->assertMessageIsDispatched(SyncReportCommand::class, false),
            'No next sync step should be rescheduled for an SES publication without a Mailchimp campaign id.',
        );
    }

    protected function getMessageRecorder(): MessageRecorderInterface
    {
        return self::getContainer()->get(MessageRecorderInterface::class);
    }
}

<?php

namespace App\Phoning\Handler;

use App\Mailer\MailerService;
use App\Mailer\Message\PhoningCampaignAdherentActionSummaryMessage;
use App\Phoning\Command\SendAdherentActionSummaryCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendAdherentActionSummaryCommandHandler implements MessageHandlerInterface
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function __invoke(SendAdherentActionSummaryCommand $command): void
    {
        $campaignHistory = $command->getCampaignHistory();

        $emailSubscribeUrl = $smsPreferenceUrl = $editProfilUrl = null;

        if ($campaignHistory->getNeedEmailRenewal()) {
            $emailSubscribeUrl = $this->urlGenerator->generate('app_user_set_email_notifications', [
                'autorun' => true,
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($campaignHistory->getNeedSmsRenewal()) {
            $smsPreferenceUrl = $this->urlGenerator->generate('app_user_set_email_notifications', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if (!$campaignHistory->isPostalCodeChecked()) {
            $editProfilUrl = $this->urlGenerator->generate('app_user_edit', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($emailSubscribeUrl || $smsPreferenceUrl || $editProfilUrl) {
            $this->transactionalMailer->sendMessage(PhoningCampaignAdherentActionSummaryMessage::create(
                $campaignHistory,
                $emailSubscribeUrl,
                $smsPreferenceUrl,
                $editProfilUrl
            ));
        }
    }
}

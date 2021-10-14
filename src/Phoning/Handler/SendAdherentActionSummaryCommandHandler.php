<?php

namespace App\Phoning\Handler;

use App\Adherent\AdherentTokenGenerator;
use App\Entity\AdherentEmailSubscribeToken;
use App\Mailer\MailerService;
use App\Mailer\Message\PhoningCampaignAdherentActionSummaryMessage;
use App\Phoning\Command\SendAdherentActionSummaryCommand;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendAdherentActionSummaryCommandHandler implements MessageHandlerInterface
{
    private MailerService $mailer;
    private AdherentTokenGenerator $adherentTokenGenerator;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        MailerService $transactionalMailer,
        AdherentTokenGenerator $adherentTokenGenerator,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->mailer = $transactionalMailer;
        $this->adherentTokenGenerator = $adherentTokenGenerator;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(SendAdherentActionSummaryCommand $command): void
    {
        $campaignHistory = $command->getCampaignHistory();
        $adherent = $campaignHistory->getAdherent();

        $emailSubscribeUrl = $smsPreferenceUrl = $editProfilUrl = null;

        if ($campaignHistory->getNeedEmailRenewal()) {
            $token = $this->adherentTokenGenerator->generateEmailSubscriptionToken($adherent, AdherentEmailSubscribeToken::TRIGGER_SOURCE_PHONING);

            $emailSubscribeUrl = $this->urlGenerator->generate('app_adherent_profile_email_subscribe', [
                'adherent_uuid' => $adherent->getUuid(),
                'email_subscribe_token' => $token->getValue(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if ($campaignHistory->getNeedSmsRenewal()) {
            $smsPreferenceUrl = $this->urlGenerator->generate('app_user_set_email_notifications', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        if (!$campaignHistory->isPostalCodeChecked()) {
            $editProfilUrl = $this->urlGenerator->generate('app_user_edit', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $this->mailer->sendMessage(PhoningCampaignAdherentActionSummaryMessage::create(
            $campaignHistory,
            $emailSubscribeUrl,
            $smsPreferenceUrl,
            $editProfilUrl
        ));
    }
}

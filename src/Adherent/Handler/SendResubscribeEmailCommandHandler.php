<?php

namespace App\Adherent\Handler;

use App\Adherent\AdherentTokenGenerator;
use App\Adherent\Command\SendResubscribeEmailCommand;
use App\Mailer\MailerService;
use App\Mailer\Message\AdherentResubscribeEmailMessage;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendResubscribeEmailCommandHandler implements MessageHandlerInterface
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

    public function __invoke(SendResubscribeEmailCommand $command): void
    {
        $adherent = $command->getAdherent();

        $token = $this->adherentTokenGenerator->generateEmailSubscriptionToken($adherent, $command->getTriggerSource());

        $this->mailer->sendMessage(AdherentResubscribeEmailMessage::create(
            $adherent,
            $this->urlGenerator->generate('app_adherent_profile_email_subscribe', [
                'adherent_uuid' => $adherent->getUuid(),
                'email_subscribe_token' => $token->getValue(),
            ], UrlGeneratorInterface::ABSOLUTE_URL)
        ));
    }
}

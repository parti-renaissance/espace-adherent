<?php

namespace App\Renaissance\Membership;

use App\Entity\Adherent;
use App\Entity\AdherentResetPasswordToken;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceAdherentAccountCreatedMessage;
use App\OAuth\App\AuthAppUrlManager;
use Doctrine\ORM\EntityManagerInterface;

class Notifier
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerService $transactionalMailer,
        private readonly AuthAppUrlManager $appUrlManager
    ) {
    }

    public function sendAccountCreatedEmail(Adherent $adherent): void
    {
        $token = AdherentResetPasswordToken::generate($adherent, '+30 days');
        $message = RenaissanceAdherentAccountCreatedMessage::create(
            $adherent,
            $this->appUrlManager->getUrlGenerator($adherent->getSource())->generateCreatePasswordLink($adherent, $token, ['is_creation' => true])
        );

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        $this->transactionalMailer->sendMessage($message);
    }
}

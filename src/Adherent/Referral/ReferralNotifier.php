<?php

namespace App\Adherent\Referral;

use App\Entity\Referral;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\Referral\ReferralAdhesionCreatedMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ReferralNotifier
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function sendAdhesionMessage(Referral $referral): void
    {
        $this->transactionalMailer->sendMessage(
            ReferralAdhesionCreatedMessage::create(
                $referral->emailAddress,
                $referral->firstName,
                $this->urlGenerator->generate('app_adhesion_index'),
                $this->urlGenerator->generate('app_referral_report', [
                    'uuid' => $referral->uuid->toString(),
                ])
            )
        );
    }
}

<?php

namespace App\Adherent\Referral;

use App\Controller\Renaissance\Referral\AdhesionController;
use App\Controller\Renaissance\Referral\Report\FormController;
use App\Entity\Referral;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\Referral\ReferralAdhesionCreatedMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Notifier
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
                $referral->referrer->getFirstName(),
                $referral->emailAddress,
                $referral->firstName,
                $this->generateUrl(
                    AdhesionController::ROUTE_NAME,
                    [
                        'identifier' => $referral->identifier,
                    ]
                ),
                $this->generateUrl(
                    FormController::ROUTE_NAME,
                    [
                        'identifier' => $referral->identifier,
                    ]
                )
            )
        );
    }

    private function generateUrl(string $routeName, array $parameters = []): string
    {
        return $this->urlGenerator->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

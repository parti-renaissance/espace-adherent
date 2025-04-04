<?php

namespace App\Adherent\Referral;

use App\Controller\Renaissance\Referral\AdhesionController;
use App\Controller\Renaissance\Referral\ReportController;
use App\Entity\Referral;
use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\Referral\ReferralAdhesionCreatedMessage;
use App\Mailer\Message\Renaissance\Referral\ReferralAdhesionFinishedMessage;
use App\Mailer\Message\Renaissance\Referral\ReferralReportedMessage;
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
                $this->generateUrl(AdhesionController::ROUTE_NAME, ['identifier' => $referral->identifier]),
                $this->generateUrl(ReportController::ROUTE_NAME, ['uuid' => $referral->getUuid()])
            )
        );
    }

    public function sendReportMessage(Referral $referral): void
    {
        if (!$referral->referrer) {
            return;
        }

        $this->transactionalMailer->sendMessage(
            ReferralReportedMessage::create(
                $referral->referrer->getEmailAddress(),
                $referral->referrer->getFirstName(),
                $referral->firstName
            )
        );
    }

    public function sendAdhesionFinishedMessage(Referral $referral): void
    {
        if (!$referral->referrer) {
            return;
        }

        $this->transactionalMailer->sendMessage(
            ReferralAdhesionFinishedMessage::create(
                $referral->referrer->getEmailAddress(),
                $referral->referrer->getFirstName(),
                $referral->firstName
            )
        );
    }

    private function generateUrl(string $routeName, array $parameters = []): string
    {
        return $this->urlGenerator->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}

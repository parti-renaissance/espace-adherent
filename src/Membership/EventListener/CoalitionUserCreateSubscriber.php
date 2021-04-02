<?php

namespace App\Membership\EventListener;

use App\Entity\Adherent;
use App\Entity\AdherentResetPasswordToken;
use App\Mailer\MailerService;
use App\Mailer\Message\Coalition\CoalitionUserAccountConfirmationMessage;
use App\Membership\MembershipSourceEnum;
use App\Membership\UserEvent;
use App\Membership\UserEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CoalitionUserCreateSubscriber implements EventSubscriberInterface
{
    private $entityManager;
    private $mailer;
    private $coalitionsHost;

    public function __construct(
        EntityManagerInterface $entityManager,
        MailerService $transactionalMailer,
        string $coalitionsHost
    ) {
        $this->entityManager = $entityManager;
        $this->mailer = $transactionalMailer;
        $this->coalitionsHost = $coalitionsHost;
    }

    public function sendConfirmationEmail(UserEvent $event): void
    {
        $adherent = $event->getUser();
        if (MembershipSourceEnum::COALITIONS === $adherent->getSource()) {
            $createPasswordToken = AdherentResetPasswordToken::generate($adherent, '+30 days');

            $this->entityManager->persist($createPasswordToken);
            $this->entityManager->flush();

            $createPasswordUrl = $this->generateCoalitionCreatePasswordUrl($adherent, $createPasswordToken);
            $this->mailer->sendMessage(CoalitionUserAccountConfirmationMessage::createFromAdherent($adherent, $createPasswordUrl));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            UserEvents::USER_CREATED => 'sendConfirmationEmail',
        ];
    }

    private function generateCoalitionCreatePasswordUrl(Adherent $adherent, AdherentResetPasswordToken $token)
    {
        return \sprintf('%s/confirmation/%s/%s',
            $this->coalitionsHost,
            (string) $adherent->getUuid(),
            (string) $token->getValue()
        );
    }
}

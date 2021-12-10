<?php

namespace App\Membership\EventListener;

use App\Coalition\CoalitionUrlGenerator;
use App\Entity\Adherent;
use App\Entity\AdherentResetPasswordToken;
use App\Mailer\MailerService;
use App\Mailer\Message\Coalition\CoalitionUserAccountConfirmationMessage;
use App\Membership\Event\UserEvent;
use App\Membership\MembershipNotifier;
use App\Membership\MembershipSourceEnum;
use App\Membership\UserEvents;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendEmailValidationSubscriber implements EventSubscriberInterface
{
    private $entityManager;
    private $mailer;
    private $coalitionUrlGenerator;
    private $notifier;

    public function __construct(
        EntityManagerInterface $entityManager,
        MailerService $transactionalMailer,
        CoalitionUrlGenerator $coalitionUrlGenerator,
        MembershipNotifier $notifier
    ) {
        $this->entityManager = $entityManager;
        $this->mailer = $transactionalMailer;
        $this->coalitionUrlGenerator = $coalitionUrlGenerator;
        $this->notifier = $notifier;
    }

    public function sendConfirmationEmail(UserEvent $event): void
    {
        $adherent = $event->getUser();

        if (null === $adherent->getSource()) {
            $this->notifier->sendEmailValidation($adherent);

            return;
        }

        if (MembershipSourceEnum::COALITIONS === $adherent->getSource()) {
            $token = AdherentResetPasswordToken::generate($adherent, '+30 days');
            $url = $this->generateCoalitionCreatePasswordUrl($adherent, $token);

            $this->entityManager->persist($token);
            $this->entityManager->flush();

            $this->mailer->sendMessage(CoalitionUserAccountConfirmationMessage::createFromAdherent($adherent, $url));
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            UserEvents::USER_CREATED => 'sendConfirmationEmail',
        ];
    }

    private function generateCoalitionCreatePasswordUrl(Adherent $adherent, AdherentResetPasswordToken $token): string
    {
        return $this->coalitionUrlGenerator->generateCreatePasswordLink($adherent, $token);
    }
}

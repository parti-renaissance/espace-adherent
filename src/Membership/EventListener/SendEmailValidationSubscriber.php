<?php

namespace App\Membership\EventListener;

use App\Entity\AdherentResetPasswordToken;
use App\Mailer\MailerService;
use App\Mailer\Message\Coalition\CoalitionUserAccountConfirmationMessage;
use App\Mailer\Message\JeMengage\JeMengageUserAccountConfirmationMessage;
use App\Membership\Event\UserEvent;
use App\Membership\MembershipNotifier;
use App\Membership\MembershipSourceEnum;
use App\Membership\UserEvents;
use App\OAuth\App\AuthAppUrlManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SendEmailValidationSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private MailerService $mailer;
    private MembershipNotifier $notifier;
    private AuthAppUrlManager $appUrlManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        MailerService $transactionalMailer,
        MembershipNotifier $notifier,
        AuthAppUrlManager $appUrlManager
    ) {
        $this->entityManager = $entityManager;
        $this->mailer = $transactionalMailer;
        $this->notifier = $notifier;
        $this->appUrlManager = $appUrlManager;
    }

    public function sendConfirmationEmail(UserEvent $event): void
    {
        $adherent = $event->getUser();

        if (null === $adherent->getSource()) {
            $this->notifier->sendEmailValidation($adherent);

            return;
        }

        $message = $token = null;

        switch ($adherent->getSource()) {
            case MembershipSourceEnum::COALITIONS:
                $token = AdherentResetPasswordToken::generate($adherent, '+30 days');
                $message = CoalitionUserAccountConfirmationMessage::createFromAdherent(
                    $adherent,
                    $this->appUrlManager->getUrlGenerator($adherent->getSource())->generateCreatePasswordLink($adherent, $token)
                );
                break;
            case MembershipSourceEnum::JEMENGAGE:
                $token = AdherentResetPasswordToken::generate($adherent);
                $message = JeMengageUserAccountConfirmationMessage::createFromAdherent(
                    $adherent,
                    $this->appUrlManager->getUrlGenerator($adherent->getSource())->generateCreatePasswordLink($adherent, $token)
                );
                break;
        }

        if ($message && $token) {
            $this->entityManager->persist($token);
            $this->entityManager->flush();

            $this->mailer->sendMessage($message);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            UserEvents::USER_CREATED => 'sendConfirmationEmail',
        ];
    }
}

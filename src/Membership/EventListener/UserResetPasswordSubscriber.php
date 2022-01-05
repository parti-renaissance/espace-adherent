<?php

namespace App\Membership\EventListener;

use App\Mailer\MailerService;
use App\Mailer\Message\AdherentResetPasswordMessage;
use App\Mailer\Message\Coalition\CoalitionResetPasswordMessage;
use App\Mailer\Message\JeMengage\JeMengageResetPasswordMessage;
use App\Membership\Event\UserResetPasswordEvent;
use App\Membership\MembershipSourceEnum;
use App\Membership\UserEvents;
use App\OAuth\App\AuthAppUrlManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserResetPasswordSubscriber implements EventSubscriberInterface
{
    private MailerService $mailer;
    private AuthAppUrlManager $appUrlManager;

    public function __construct(MailerService $transactionalMailer, AuthAppUrlManager $appUrlManager)
    {
        $this->mailer = $transactionalMailer;
        $this->appUrlManager = $appUrlManager;
    }

    public function publishUserResetPassword(UserResetPasswordEvent $event): void
    {
        $user = $event->getUser();
        $resetPasswordToken = $event->getResetPasswordToken();
        $resetPasswordUrl = $this->appUrlManager->getUrlGenerator($source = $event->getSource())->generateCreatePasswordLink($user, $resetPasswordToken);

        switch ($source) {
            case MembershipSourceEnum::COALITIONS:
                $message = CoalitionResetPasswordMessage::createFromAdherent($user, $resetPasswordUrl);
                break;
            case MembershipSourceEnum::JEMENGAGE:
                $message = JeMengageResetPasswordMessage::createFromAdherent($user, $resetPasswordUrl);
                break;
            case MembershipSourceEnum::PLATFORM:
                $message = AdherentResetPasswordMessage::createFromAdherent($user, $resetPasswordUrl);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Invalid adherent source "%s"', $source));
        }

        $this->mailer->sendMessage($message);
    }

    public static function getSubscribedEvents()
    {
        return [
            UserEvents::USER_FORGOT_PASSWORD => 'publishUserResetPassword',
        ];
    }
}

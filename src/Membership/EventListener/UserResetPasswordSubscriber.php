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
use Symfony\Component\HttpFoundation\RequestStack;

class UserResetPasswordSubscriber implements EventSubscriberInterface
{
    private MailerService $mailer;
    private RequestStack $requestStack;
    private AuthAppUrlManager $appUrlManager;

    public function __construct(
        MailerService $transactionalMailer,
        RequestStack $requestStack,
        AuthAppUrlManager $appUrlManager
    ) {
        $this->mailer = $transactionalMailer;
        $this->requestStack = $requestStack;
        $this->appUrlManager = $appUrlManager;
    }

    public function publishUserResetPassword(UserResetPasswordEvent $event): void
    {
        $user = $event->getUser();
        $resetPasswordToken = $event->getResetPasswordToken();
        $currentApp = $this->getCurrentApp();
        $resetPasswordUrl = $this->appUrlManager->getUrlGenerator($currentApp)->generateCreatePasswordLink($user, $resetPasswordToken);

        switch ($currentApp) {
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
                throw new \InvalidArgumentException(sprintf('Invalid adherent source "%s"', $currentApp));
        }

        $this->mailer->sendMessage($message);
    }

    public static function getSubscribedEvents()
    {
        return [
            UserEvents::USER_FORGOT_PASSWORD => 'publishUserResetPassword',
        ];
    }

    private function getCurrentApp(): string
    {
        return $this->requestStack->getCurrentRequest()->attributes->get('app', MembershipSourceEnum::PLATFORM);
    }
}

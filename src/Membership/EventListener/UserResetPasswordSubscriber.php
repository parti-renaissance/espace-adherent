<?php

declare(strict_types=1);

namespace App\Membership\EventListener;

use App\Mailer\MailerService;
use App\Mailer\Message\Renaissance\RenaissanceResetPasswordMessage;
use App\Membership\Event\UserResetPasswordEvent;
use App\Membership\UserEvents;
use App\OAuth\App\AuthAppUrlManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserResetPasswordSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly AuthAppUrlManager $appUrlManager,
    ) {
    }

    public function publishUserResetPassword(UserResetPasswordEvent $event): void
    {
        $user = $event->getUser();
        $resetPasswordToken = $event->getResetPasswordToken();
        $resetPasswordUrl = $this->appUrlManager->getUrlGenerator($source = $event->getSource())->generateCreatePasswordLink($user, $resetPasswordToken);

        $this->transactionalMailer->sendMessage(RenaissanceResetPasswordMessage::createFromAdherent($user, $resetPasswordUrl));
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_FORGOT_PASSWORD => 'publishUserResetPassword',
        ];
    }
}

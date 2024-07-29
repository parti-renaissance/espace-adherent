<?php

namespace App\Membership\EventListener;

use App\Mailer\MailerService;
use App\Mailer\Message\AdherentResetPasswordMessage;
use App\Mailer\Message\BesoinDEurope\BesoinDEuropeResetPasswordMessage;
use App\Mailer\Message\Ensemble\EnsembleResetPasswordMessage;
use App\Mailer\Message\JeMengage\JeMengageResetPasswordMessage;
use App\Mailer\Message\Renaissance\RenaissanceResetPasswordMessage;
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

        $message = match ($source) {
            MembershipSourceEnum::JEMENGAGE => JeMengageResetPasswordMessage::createFromAdherent($user, $resetPasswordUrl),
            MembershipSourceEnum::RENAISSANCE => RenaissanceResetPasswordMessage::createFromAdherent($user, $resetPasswordUrl),
            MembershipSourceEnum::BESOIN_D_EUROPE => BesoinDEuropeResetPasswordMessage::createFromAdherent($user, $resetPasswordUrl),
            MembershipSourceEnum::PLATFORM => AdherentResetPasswordMessage::createFromAdherent($user, $resetPasswordUrl),
            MembershipSourceEnum::LEGISLATIVE => EnsembleResetPasswordMessage::createFromAdherent($user, $resetPasswordUrl),
            default => throw new \InvalidArgumentException(\sprintf('Invalid adherent source "%s"', $source)),
        };

        $this->mailer->sendMessage($message);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserEvents::USER_FORGOT_PASSWORD => 'publishUserResetPassword',
        ];
    }
}

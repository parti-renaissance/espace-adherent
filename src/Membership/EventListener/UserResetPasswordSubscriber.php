<?php

namespace App\Membership\EventListener;

use App\Coalition\CoalitionUrlGenerator;
use App\Entity\Adherent;
use App\Entity\AdherentResetPasswordToken;
use App\Mailer\MailerService;
use App\Mailer\Message\AdherentResetPasswordMessage;
use App\Mailer\Message\Coalition\CoalitionResetPasswordMessage;
use App\Membership\Event\UserResetPasswordEvent;
use App\Membership\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserResetPasswordSubscriber implements EventSubscriberInterface
{
    private $mailer;
    private $requestStack;
    private $urlGenerator;
    private $coalitionUrlGenerator;

    public function __construct(
        MailerService $transactionalMailer,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        CoalitionUrlGenerator $coalitionUrlGenerator
    ) {
        $this->mailer = $transactionalMailer;
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
        $this->coalitionUrlGenerator = $coalitionUrlGenerator;
    }

    public function publishUserResetPassword(UserResetPasswordEvent $event): void
    {
        $user = $event->getUser();
        $resetPasswordToken = $event->getResetPasswordToken();

        if ($this->isCoalitionRequest()) {
            $resetPasswordUrl = $this->generateCoalitionResetPasswordUrl($user, $resetPasswordToken);
            $message = CoalitionResetPasswordMessage::createFromAdherent($user, $resetPasswordUrl);
        } else {
            $resetPasswordUrl = $this->generateAdherentResetPasswordUrl($user, $resetPasswordToken);
            $message = AdherentResetPasswordMessage::createFromAdherent($user, $resetPasswordUrl);
        }

        $this->mailer->sendMessage($message);
    }

    public static function getSubscribedEvents()
    {
        return [
            UserEvents::USER_FORGOT_PASSWORD => 'publishUserResetPassword',
        ];
    }

    private function isCoalitionRequest(): bool
    {
        return $this->requestStack->getCurrentRequest()->attributes->getBoolean('coalition');
    }

    private function generateAdherentResetPasswordUrl(Adherent $adherent, AdherentResetPasswordToken $token): string
    {
        $params = [
            'adherent_uuid' => (string) $adherent->getUuid(),
            'reset_password_token' => (string) $token->getValue(),
        ];

        return $this->urlGenerator->generate('adherent_reset_password', $params, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    private function generateCoalitionResetPasswordUrl(Adherent $adherent, AdherentResetPasswordToken $token): string
    {
        return $this->coalitionUrlGenerator->generateCreatePasswordLink($adherent, $token);
    }
}

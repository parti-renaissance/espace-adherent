<?php

namespace App\History\Listener;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\History\UserActionHistoryHandler;
use App\Membership\Event\UserEmailEvent;
use App\Membership\Event\UserEvent;
use App\Membership\Event\UserResetPasswordEvent;
use App\Membership\UserEvents;
use App\Utils\ArrayUtils;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserActionHistorySubscriber implements EventSubscriberInterface
{
    private array $userBeforeUpdate = [];

    public function __construct(
        private readonly Security $security,
        private readonly NormalizerInterface $normalizer,
        private readonly UserActionHistoryHandler $userActionHistoryHandler,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InteractiveLoginEvent::class => ['onInteractiveLogin', -4096],
            LoginFailureEvent::class => ['onLoginFailure', -4096],
            SwitchUserEvent::class => ['onSwitchUser', -4096],
            UserEvents::USER_PROFILE_BEFORE_UPDATE => ['onProfileBeforeUpdate', -4096],
            UserEvents::USER_PROFILE_AFTER_UPDATE => ['onProfileAfterUpdate', -4096],
            UserEvents::USER_FORGOT_PASSWORD => ['onPasswordResetRequest', -4096],
            UserEvents::USER_FORGOT_PASSWORD_VALIDATED => ['onPasswordResetValidate', -4096],
            UserEvents::USER_EMAIL_CHANGE_REQUEST => ['onEmailChangeRequest', -4096],
            UserEvents::USER_EMAIL_UPDATED => ['onEmailChangeValidate', -4096],
        ];
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof Adherent) {
            return;
        }

        $this->userActionHistoryHandler->createLoginSuccess($user);
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        /** @var UserBadge|null $userBadge */
        $userBadge = $event->getPassport()?->getBadge(UserBadge::class);

        $user = $userBadge?->getUser();

        if (!$user instanceof Adherent) {
            return;
        }

        $this->userActionHistoryHandler->createLoginFailure($user);
    }

    public function onSwitchUser(SwitchUserEvent $event): void
    {
        $user = $this->security->getUser();
        $targetUser = $event->getToken()?->getUser();

        if ($user instanceof Administrator && $targetUser instanceof Adherent) {
            $this->userActionHistoryHandler->createImpersonationStart($user, $targetUser);

            return;
        }

        if ($user instanceof Adherent && $targetUser instanceof Administrator) {
            $this->userActionHistoryHandler->createImpersonationEnd($user, $targetUser);
        }
    }

    public function onProfileBeforeUpdate(UserEvent $event): void
    {
        $this->userBeforeUpdate = $this->transformToArray($event->getAdherent());
    }

    public function onProfileAfterUpdate(UserEvent $event): void
    {
        $diff = array_keys(
            ArrayUtils::arrayDiffRecursive(
                $this->userBeforeUpdate,
                $this->transformToArray($event->getAdherent())
            )
        );

        if (empty($diff)) {
            return;
        }

        $this->userActionHistoryHandler->createProfileUpdate($event->getAdherent(), $diff);
    }

    public function onPasswordResetRequest(UserResetPasswordEvent $event): void
    {
        $this->userActionHistoryHandler->createPasswordResetRequest($event->getUser());
    }

    public function onPasswordResetValidate(UserEvent $event): void
    {
        $this->userActionHistoryHandler->createPasswordResetValidate($event->getAdherent());
    }

    public function onEmailChangeRequest(UserEvent $event): void
    {
        $this->userActionHistoryHandler->createEmailChangeRequest($event->getAdherent());
    }

    public function onEmailChangeValidate(UserEmailEvent $event): void
    {
        $this->userActionHistoryHandler->createEmailChangeValidate($event->getUser());
    }

    private function transformToArray(Adherent $adherent): array
    {
        $data = $this->normalizer->normalize(
            $adherent,
            'array',
            [
                'groups' => [
                    'profile_update',
                ],
            ]
        );

        if (isset($data['zones']) && \is_array($data['zones'])) {
            usort($data['zones'], function ($a, $b) {
                return $a['code'] <=> $b['code'];
            });
        }

        return $data;
    }
}

<?php

namespace App\History\Listener;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\History\UserActionHistoryHandler;
use App\Membership\Event\UserEvent;
use App\Membership\Event\UserResetPasswordEvent;
use App\Membership\UserEvents;
use App\Utils\ArrayUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserActionHistorySubscriber implements EventSubscriberInterface
{
    private array $userBeforeUpdate = [];

    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly UserActionHistoryHandler $userActionHistoryHandler,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationSuccessEvent::class => ['onLoginSuccess', -4096],
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

    public function onLoginSuccess(): void
    {
        $this->userActionHistoryHandler->createLoginSuccess();
    }

    public function onLoginFailure(): void
    {
        $this->userActionHistoryHandler->createLoginFailure();
    }

    public function onSwitchUser(SwitchUserEvent $event): void
    {
        $targetUser = $event->getTargetUser();

        if ($targetUser instanceof Adherent) {
            $this->userActionHistoryHandler->createImpersonificationStart($targetUser);

            return;
        }

        if ($targetUser instanceof Administrator) {
            $this->userActionHistoryHandler->createImpersonificationEnd($targetUser);
        }
    }

    public function onProfileBeforeUpdate(UserEvent $event): void
    {
        $this->userBeforeUpdate = $this->transformToArray($event->getUser());
    }

    public function onProfileAfterUpdate(UserEvent $event): void
    {
        $diff = array_keys(
            ArrayUtils::arrayDiffRecursive(
                $this->userBeforeUpdate,
                $this->transformToArray($event->getUser())
            )
        );

        if (empty($diff)) {
            return;
        }

        $this->userActionHistoryHandler->createProfileUpdate($diff);
    }

    public function onPasswordResetRequest(UserResetPasswordEvent $event): void
    {
        $this->userActionHistoryHandler->createPasswordResetRequest($event->getUser());
    }

    public function onPasswordResetValidate(UserEvent $event): void
    {
        $this->userActionHistoryHandler->createPasswordResetValidate($event->getUser());
    }

    public function onEmailChangeRequest(): void
    {
        $this->userActionHistoryHandler->createEmailChangeRequest();
    }

    public function onEmailChangeValidate(): void
    {
        $this->userActionHistoryHandler->createEmailChangeValidate();
    }

    private function transformToArray(Adherent $adherent): array
    {
        return $this->normalizer->normalize(
            $adherent,
            'array',
            [
                'groups' => [
                    'profile_write',
                    'uncertified_profile_write',
                ],
            ]
        );
    }
}

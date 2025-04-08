<?php

namespace App\History\Listener;

use App\Entity\Adherent;
use App\Entity\Administrator;
use App\History\AdministratorActionEvent;
use App\History\AdministratorActionEvents;
use App\History\AdministratorActionHistoryHandler;
use App\Utils\ArrayUtils;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AdministratorActionHistorySubscriber implements EventSubscriberInterface
{
    private array $userBeforeUpdate = [];

    public function __construct(
        private readonly Security $security,
        private readonly NormalizerInterface $normalizer,
        private readonly AdministratorActionHistoryHandler $administratorActionHistoryHandler,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            InteractiveLoginEvent::class => ['onInteractiveLogin', -4096],
            LoginFailureEvent::class => ['onLoginFailure', -4096],
            SwitchUserEvent::class => ['onSwitchUser', -4096],
            KernelEvents::RESPONSE => ['onKernelResponse', -4096],
            AdministratorActionEvents::ADMIN_USER_PROFILE_BEFORE_UPDATE => ['onAdherentProfileBeforeUpdate', -4096],
            AdministratorActionEvents::ADMIN_USER_PROFILE_AFTER_UPDATE => ['onAdherentProfileAfterUpdate', -4096],
        ];
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof Administrator) {
            return;
        }

        $this->administratorActionHistoryHandler->createLoginSuccess($user);
    }

    public function onLoginFailure(LoginFailureEvent $event): void
    {
        /** @var UserBadge|null $userBadge */
        $userBadge = $event->getPassport()?->getBadge(UserBadge::class);

        $user = $userBadge?->getUser();

        if (!$user instanceof Administrator) {
            return;
        }

        $this->administratorActionHistoryHandler->createLoginFailure($user);
    }

    public function onSwitchUser(SwitchUserEvent $event): void
    {
        $user = $this->security->getUser();
        $targetUser = $event->getToken()?->getUser();

        if ($user instanceof Administrator && $targetUser instanceof Adherent) {
            $this->administratorActionHistoryHandler->createImpersonationStart($user, $targetUser);

            return;
        }

        if ($user instanceof Adherent && $targetUser instanceof Administrator) {
            $this->administratorActionHistoryHandler->createImpersonationEnd($user, $targetUser);
        }
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $routeName = $request->get('_route');

        if (!$routeName || !preg_match('/^admin_(.)+_export$/', $routeName)) {
            return;
        }

        $administrator = $this->security->getUser();

        if (!$administrator instanceof Administrator) {
            return;
        }

        $this->administratorActionHistoryHandler->createExport($administrator, $routeName, $request->query->all());
    }

    public function onAdherentProfileBeforeUpdate(AdministratorActionEvent $event): void
    {
        if (!$adherent = $event->adherent) {
            return;
        }

        $this->userBeforeUpdate = $this->transformAdherentToArray($adherent);
    }

    public function onAdherentProfileAfterUpdate(AdministratorActionEvent $event): void
    {
        if (!$adherent = $event->adherent) {
            return;
        }

        $diff = array_keys(
            ArrayUtils::arrayDiffRecursive(
                $this->userBeforeUpdate,
                $this->transformAdherentToArray($adherent),
                true
            )
        );

        if (empty($diff)) {
            return;
        }

        $this->administratorActionHistoryHandler->createAdherentProfileUpdate($event->administrator, $adherent, $diff);
    }

    private function transformAdherentToArray(Adherent $adherent): array
    {
        return $this->normalizer->normalize(
            $adherent,
            'array',
            [
                'groups' => [
                    'profile_update',
                ],
            ]
        );
    }
}

<?php

namespace App\Security\Listener;

use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Migrate the user to the new hashing algorithm if is using the legacy one.
 */
class LegacyMigrationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly EncoderFactoryInterface $encoderFactory,
        private readonly EntityManagerInterface $manager,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [InteractiveLoginEvent::class => 'onSecurityInteractiveLogin'];
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof Adherent) {
            return;
        }

        $request = $event->getRequest();

        if ('app_user_login_check' !== $request->attributes->get('_route')) {
            return;
        }

        $token = $event->getAuthenticationToken();

        if ($user->hasLegacyPassword()) {
            $user->clearOldPassword();

            $encoder = $this->encoderFactory->getEncoder($user);
            $user->migratePassword(
                $encoder->encodePassword($event->getRequest()->request->get('_login_password'), null)
            );

            $this->manager->persist($user);
            $this->manager->flush();
        }

        $token->eraseCredentials();
    }
}

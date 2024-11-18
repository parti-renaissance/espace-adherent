<?php

namespace App\Security\Listener;

use App\Entity\Adherent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Migrate the user to the new hashing algorithm if is using the legacy one.
 */
class LegacyMigrationListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
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

        $token = $event->getAuthenticationToken();

        if ($user->hasLegacyPassword()) {
            $user->clearOldPassword();

            $user->migratePassword(
                $this->hasher->hashPassword($user, $event->getRequest()->request->get('_password'))
            );

            $this->manager->persist($user);
            $this->manager->flush();
        }

        $token->eraseCredentials();
    }
}

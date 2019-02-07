<?php

namespace AppBundle\Security;

use AppBundle\Entity\Adherent;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * Migrate the user to the new hashing algorithm if is using the legacy one.
 */
class LegacyMigrationListener
{
    private $encoderFactory;
    private $manager;

    public function __construct(EncoderFactoryInterface $encoderFactory, ObjectManager $manager)
    {
        $this->encoderFactory = $encoderFactory;
        $this->manager = $manager;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof Adherent) {
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

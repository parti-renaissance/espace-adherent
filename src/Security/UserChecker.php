<?php

namespace AppBundle\Security;

use AppBundle\Entity\Adherent;
use AppBundle\Exception\AccountNotValidatedException;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function checkPreAuth(UserInterface $user)
    {
    }

    public function checkPostAuth(UserInterface $user)
    {
        /** @var Adherent $user */
        if (!$user instanceof Adherent) {
            throw new \UnexpectedValueException('You have to pass an Adherent instance.');
        }

        if (!$user->isEnabled()) {
            if ($user->isToDelete()) {
                throw new UsernameNotFoundException();
            }

            if ($user->getActivatedAt()) {
                $ex = new DisabledException('Account disabled.');
                $ex->setUser($user);
                throw $ex;
            }

            throw new AccountNotValidatedException($user, $this->router->generate('adherent_resend_validation'));
        }
    }
}

<?php

namespace AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Role\SwitchUserRole;
use Symfony\Component\Security\Core\User\UserInterface;

final class AuthenticationUtils
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function getImpersonatingUser(): ?UserInterface
    {
        $impersonatingUser = null;

        foreach ($this->tokenStorage->getToken()->getRoles() as $role) {
            if ($role instanceof SwitchUserRole) {
                $impersonatingUser = $role->getSource()->getUser();
                break;
            }
        }

        return $impersonatingUser;
    }

    public function authenticateUser(UserInterface $user)
    {
        $this->tokenStorage->setToken(new UsernamePasswordToken($user, '', 'admins_db', $user->getRoles()));
    }
}

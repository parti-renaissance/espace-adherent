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

    public function authenticateAdmin(UserInterface $user)
    {
        $this->doAuthenticateUser($user, 'admins_db');
    }

    public function authenticateAdherent(UserInterface $user)
    {
        $this->doAuthenticateUser($user, 'users_db');
    }

    private function doAuthenticateUser(UserInterface $user, string $provider)
    {
        return $this->tokenStorage->setToken(new UsernamePasswordToken($user, '', $provider, $user->getRoles()));
    }
}

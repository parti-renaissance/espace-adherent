<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

final class AuthenticationUtils
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function authenticateAdherent(UserInterface $user)
    {
        $this->doAuthenticateUser($user, 'main');
    }

    private function doAuthenticateUser(UserInterface $user, string $firewallName)
    {
        return $this->tokenStorage->setToken(new PostAuthenticationGuardToken($user, $firewallName, $user->getRoles()));
    }
}

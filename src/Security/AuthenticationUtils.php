<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

final class AuthenticationUtils
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function authenticateAdherent(UserInterface $user): void
    {
        $this->tokenStorage->setToken(null);
        $this->doAuthenticateUser($user);
    }

    private function doAuthenticateUser(UserInterface $user): void
    {
        $this->tokenStorage->setToken(new PostAuthenticationGuardToken($user, 'main', $user->getRoles()));
    }
}

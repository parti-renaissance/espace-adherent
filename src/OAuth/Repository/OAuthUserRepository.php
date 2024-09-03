<?php

namespace App\OAuth\Repository;

use App\Security\UserProvider;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class OAuthUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly UserProvider $userProvider,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
    ) {
    }

    public function getUserEntityByUserCredentials(
        $username,
        $password,
        $grantType,
        ClientEntityInterface $clientEntity,
    ): ?UserInterface {
        try {
            $user = $this->userProvider->loadUserByIdentifier($username);
        } catch (AuthenticationException $e) {
            return null;
        }

        if (null === $user || !($user instanceof PasswordAuthenticatedUserInterface)) {
            return null;
        }

        if (!$this->userPasswordHasher->isPasswordValid($user, $password)) {
            return null;
        }

        return $user;
    }
}

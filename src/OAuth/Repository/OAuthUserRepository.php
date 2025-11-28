<?php

declare(strict_types=1);

namespace App\OAuth\Repository;

use App\Entity\Adherent;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\UserRepositoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly UserProviderInterface $userProvider,
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

        if (!$user instanceof Adherent) {
            return null;
        }

        if (!$this->userPasswordHasher->isPasswordValid($user, $password)) {
            return null;
        }

        return $user;
    }
}

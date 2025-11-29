<?php

declare(strict_types=1);

namespace App\OAuth\Model;

use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractApiUser implements UserInterface
{
    /**
     * @var string
     */
    private $uuid;

    /**
     * @var string[]
     */
    private $roles = ['ROLE_OAUTH_CLIENT'];

    /**
     * @var string
     */
    private $username = 'oauth_client_user_';

    public function __construct(string $uuid, array $roles)
    {
        $this->uuid = $uuid;
        $this->username .= $uuid;

        foreach ($roles as $role) {
            $this->roles[] = $role;
        }
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
    }
}

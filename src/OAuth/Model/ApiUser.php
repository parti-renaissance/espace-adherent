<?php

namespace AppBundle\OAuth\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class ApiUser implements UserInterface
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

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return '';
    }

    public function getSalt()
    {
        return '';
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
    }
}

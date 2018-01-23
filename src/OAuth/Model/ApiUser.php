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

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }
}

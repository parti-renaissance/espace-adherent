<?php

namespace App\OAuth\Model;

use App\Entity\Device;
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

    /**
     * @var Device|null
     */
    private $device;

    public function __construct(string $uuid, array $roles, Device $device = null)
    {
        $this->uuid = $uuid;
        $this->username .= $uuid;

        foreach ($roles as $role) {
            $this->roles[] = $role;
        }

        $this->device = $device;
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

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function isDevice(): bool
    {
        return null !== $this->device;
    }
}

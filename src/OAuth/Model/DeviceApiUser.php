<?php

declare(strict_types=1);

namespace App\OAuth\Model;

use App\Entity\Device;

class DeviceApiUser extends AbstractApiUser
{
    private const DEVICE_ROLE = 'ROLE_OAUTH_DEVICE';

    /**
     * @var Device|null
     */
    private $device;

    public function __construct(string $uuid, array $roles, Device $device)
    {
        if (!\in_array(self::DEVICE_ROLE, $roles)) {
            $roles[] = self::DEVICE_ROLE;
        }

        parent::__construct($uuid, $roles);

        $this->device = $device;
    }

    public function getDevice(): Device
    {
        return $this->device;
    }
}

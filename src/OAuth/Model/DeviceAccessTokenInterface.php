<?php

namespace App\OAuth\Model;

use League\OAuth2\Server\Entities\AccessTokenEntityInterface;

interface DeviceAccessTokenInterface extends AccessTokenEntityInterface
{
    public function getDeviceIdentifier(): ?string;

    public function setDeviceIdentifier(?string $deviceIdentifier): void;
}

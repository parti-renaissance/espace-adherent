<?php

namespace App\OAuth\Model;

use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;

final class AccessToken extends AbstractGrantToken implements DeviceAccessTokenInterface
{
    use AccessTokenTrait;

    /**
     * @var string|null
     */
    private $deviceIdentifier;

    public function getDeviceIdentifier(): ?string
    {
        return $this->deviceIdentifier;
    }

    public function setDeviceIdentifier(?string $deviceIdentifier): void
    {
        $this->deviceIdentifier = $deviceIdentifier;
    }
}

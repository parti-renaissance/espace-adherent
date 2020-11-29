<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(name="devices", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="devices_uuid_unique", columns="uuid")
 * })
 * @ORM\Entity(repositoryClass="App\Repository\DeviceRepository")
 */
class Device
{
    use EntityIdentityTrait;

    public function __construct(UuidInterface $uuid)
    {
        $this->uuid = $uuid;
    }
}

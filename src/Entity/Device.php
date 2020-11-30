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
    use EntityTimestampableTrait;

    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     */
    protected $deviceUuid;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLoggedAt;

    public function __construct(UuidInterface $uuid, UuidInterface $deviceUuid)
    {
        $this->uuid = $uuid;
        $this->deviceUuid = $deviceUuid;
    }

    public function getLastLoggedAt(): ?\DateTimeInterface
    {
        return $this->lastLoggedAt;
    }

    public function login(): void
    {
        $this->lastLoggedAt = new \DateTime('now');
    }

    public function getDeviceUuid(): UuidInterface
    {
        return $this->deviceUuid;
    }

    public function getIdentifier(): string
    {
        return $this->deviceUuid->toString();
    }
}

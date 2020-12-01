<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;

/**
 * @ORM\Table(name="devices", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="devices_uuid_unique", columns="uuid"),
 *     @ORM\UniqueConstraint(name="devices_device_uuid_unique", columns="device_uuid")
 * })
 * @ORM\Entity(repositoryClass="App\Repository\DeviceRepository")
 */
class Device
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var string
     *
     * @ORM\Column(unique=true)
     *
     * @SymfonySerializer\Groups("user_profile")
     */
    protected $deviceUuid;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLoggedAt;

    public function __construct(UuidInterface $uuid, string $deviceUuid)
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

    public function getDeviceUuid(): string
    {
        return $this->deviceUuid;
    }

    public function getIdentifier(): string
    {
        return $this->getDeviceUuid();
    }
}

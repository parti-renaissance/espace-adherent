<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use App\Collection\ZoneCollection;
use App\Repository\DeviceRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/v3/device/{deviceUuid}',
            requirements: ['deviceUuid' => '[\w-]+'],
            security: "is_granted('ROLE_OAUTH_DEVICE') and object.equals(user.getDevice())"
        ),
        new Put(
            uriTemplate: '/v3/device/{deviceUuid}',
            requirements: ['deviceUuid' => '[\w-]+'],
            security: "is_granted('ROLE_OAUTH_DEVICE') and object.equals(user.getDevice())"
        ),
    ],
    normalizationContext: ['groups' => ['device_read']],
    denormalizationContext: ['groups' => ['device_write']]
)]
#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ORM\Table(name: 'devices')]
class Device
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityZoneTrait;

    /**
     * @var UuidInterface
     */
    #[ApiProperty(identifier: false)]
    #[Groups(['user_profile'])]
    #[ORM\Column(type: 'uuid', unique: true)]
    protected $uuid;

    /**
     * @var string
     */
    #[ApiProperty(identifier: true)]
    #[Groups(['user_profile'])]
    #[ORM\Column(unique: true)]
    protected $deviceUuid;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 15)]
    #[Groups(['user_profile', 'device_write'])]
    #[ORM\Column(length: 15, nullable: true)]
    private $postalCode;

    /**
     * @var \DateTimeInterface|null
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $lastLoggedAt;

    public function __construct(UuidInterface $uuid, string $deviceUuid, ?string $postalCode = null)
    {
        $this->uuid = $uuid;
        $this->deviceUuid = $deviceUuid;
        $this->postalCode = $postalCode;

        $this->zones = new ZoneCollection();
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

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): void
    {
        $this->postalCode = $postalCode;
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->getUuid());
    }
}

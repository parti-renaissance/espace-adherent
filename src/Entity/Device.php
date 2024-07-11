<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Collection\ZoneCollection;
use App\Repository\DeviceRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'devices')]
#[ORM\Entity(repositoryClass: DeviceRepository::class)]
#[ApiResource(attributes: ['normalization_context' => ['groups' => ['device_read']], 'denormalization_context' => ['groups' => ['device_write']]], collectionOperations: [], itemOperations: ['get' => ['path' => '/v3/device/{deviceUuid}', 'requirements' => ['deviceUuid' => '[\w-]+'], 'security' => "is_granted('ROLE_OAUTH_DEVICE') and object.equals(user.getDevice())"], 'put' => ['path' => '/v3/device/{deviceUuid}', 'requirements' => ['deviceUuid' => '[\w-]+'], 'security' => "is_granted('ROLE_OAUTH_DEVICE') and object.equals(user.getDevice())"]])]
class Device
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityZoneTrait;

    /**
     * @var UuidInterface
     */
    #[Groups(['user_profile'])]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ApiProperty(identifier: false)]
    protected $uuid;

    /**
     * @var string
     */
    #[Groups(['user_profile'])]
    #[ORM\Column(unique: true)]
    #[ApiProperty(identifier: true)]
    protected $deviceUuid;

    /**
     * @var string|null
     */
    #[Groups(['user_profile', 'device_write'])]
    #[ORM\Column(length: 15, nullable: true)]
    #[Assert\Length(max: 15)]
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

<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Controller\Api\PushToken\CreateController;
use App\Controller\Api\PushToken\UnsubscribeController;
use App\Repository\PushTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/v3/push-token',
            controller: CreateController::class
        ),
        new Post(
            uriTemplate: '/v3/push-token/unsubscribe',
            controller: UnsubscribeController::class,
            deserialize: false,
        ),
    ],
    normalizationContext: ['groups' => ['push_token_read']],
    denormalizationContext: ['groups' => ['push_token_write']]
)]
#[ORM\Entity(repositoryClass: PushTokenRepository::class)]
class PushToken
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var UuidInterface
     */
    #[ApiProperty(identifier: false)]
    #[ORM\Column(type: 'uuid', unique: true)]
    protected $uuid;

    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $adherent = null;

    /**
     * @var Device|null
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Device::class)]
    private $device;

    #[ApiProperty(identifier: true)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['push_token_write'])]
    #[ORM\Column(unique: true)]
    public ?string $identifier = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $lastActivityDate = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    public ?\DateTime $unsubscribedAt = null;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?Adherent $adherent = null,
        ?Device $device = null,
        ?string $identifier = null,
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->adherent = $adherent;
        $this->device = $device;
        $this->identifier = $identifier;
    }

    public static function createForAdherent(
        UuidInterface $uuid,
        Adherent $adherent,
        string $identifier,
    ): self {
        return new self($uuid, $adherent, null, $identifier);
    }

    public static function createForDevice(
        UuidInterface $uuid,
        Device $device,
        string $identifier,
    ): self {
        return new self($uuid, null, $device, $identifier);
    }

    public function isSubscribed(): bool
    {
        return null === $this->unsubscribedAt;
    }
}

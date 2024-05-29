<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\PushToken\PushTokenSourceEnum;
use App\Repository\PushTokenRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ApiResource(
 *     attributes={
 *         "normalization_context": {
 *             "groups": {"push_token_read"}
 *         },
 *         "denormalization_context": {
 *             "groups": {"push_token_write"}
 *         },
 *     },
 *     collectionOperations={
 *         "post": {
 *             "path": "/v3/push-token"
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/push-token/{identifier}",
 *             "security": "is_granted('IS_AUTHOR_OF_PUSH_TOKEN', object)"
 *         },
 *         "delete": {
 *             "path": "/v3/push-token/{identifier}",
 *             "security": "is_granted('IS_AUTHOR_OF_PUSH_TOKEN', object)"
 *         },
 *     }
 * )
 *
 * @UniqueEntity("identifier")
 */
#[ORM\Entity(repositoryClass: PushTokenRepository::class)]
class PushToken
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var UuidInterface
     *
     * @ApiProperty(identifier=false)
     */
    #[ORM\Column(type: 'uuid', unique: true)]
    protected $uuid;

    /**
     * @var Adherent|null
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    private $adherent;

    /**
     * @var Device|null
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Device::class)]
    private $device;

    /**
     * @var string|null
     *
     * @ApiProperty(identifier=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    #[Groups(['push_token_write'])]
    #[ORM\Column(unique: true)]
    private $identifier;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @Assert\Choice(choices=PushTokenSourceEnum::ALL)
     */
    #[Groups(['push_token_write'])]
    #[ORM\Column]
    private $source;

    public function __construct(
        ?UuidInterface $uuid = null,
        ?Adherent $adherent = null,
        ?Device $device = null,
        ?string $identifier = null,
        ?string $source = null
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->adherent = $adherent;
        $this->device = $device;
        $this->identifier = $identifier;
        $this->source = $source;
    }

    public static function createForAdherent(
        UuidInterface $uuid,
        Adherent $adherent,
        string $identifier,
        string $source
    ): self {
        return new self($uuid, $adherent, null, $identifier, $source);
    }

    public static function createForDevice(
        UuidInterface $uuid,
        Device $device,
        string $identifier,
        string $source
    ): self {
        return new self($uuid, null, $device, $identifier, $source);
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent): void
    {
        $this->adherent = $adherent;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(?Device $device): void
    {
        $this->device = $device;
    }

    /**
     * @Assert\Callback
     */
    public function validateOneFieldNotBlank(ExecutionContextInterface $context): void
    {
        if (!$this->adherent && !$this->device) {
            $context->addViolation('Token must be linked to an adherent or a device.');
        }
    }
}

<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\PushToken\PushTokenSourceEnum;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as SymfonySerializer;
use Symfony\Component\Validator\Constraints as Assert;

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
 *             "method": "POST",
 *             "denormalization_context": {"api_allow_update": false},
 *             "path": "/v3/push-token"
 *         }
 *     },
 *     itemOperations={
 *         "get": {
 *             "path": "/v3/push-token/{id}",
 *             "requirements": {"id": "[\w-]+"},
 *             "access_control": "object.getAdherent() == user",
 *         },
 *         "delete": {
 *             "path": "/v3/push-token/{id}",
 *             "requirements": {"id": "[\w-]+"},
 *             "access_control": "object.getAdherent() == user",
 *         },
 *     }
 * )
 *
 * @ORM\Entity
 *
 * @UniqueEntity("identifier")
 */
class PushToken implements AuthorInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     *
     * @ApiProperty(identifier=false)
     */
    protected $uuid;

    /**
     * @var Adherent|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Adherent")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $adherent;

    /**
     * @var string|null
     *
     * @ORM\Column(unique=true)
     *
     * @ApiProperty(identifier=true)
     *
     * @SymfonySerializer\Groups({"push_token_read", "push_token_write"})
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $identifier;

    /**
     * @var string|null
     *
     * @ORM\Column
     *
     * @SymfonySerializer\Groups({"push_token_read", "push_token_write"})
     *
     * @Assert\Choice(choices=PushTokenSourceEnum::ALL)
     */
    private $source;

    public function __construct(
        UuidInterface $uuid = null,
        Adherent $adherent = null,
        string $identifier = null,
        string $source = null
    ) {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->adherent = $adherent;
        $this->identifier = $identifier;
        $this->source = $source;
    }

    public static function create(Adherent $adherent, string $identifier, string $source): self
    {
        return new self(Uuid::uuid4(), $adherent, $identifier, $source);
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

    public function setAuthor(Adherent $adherent): void
    {
        $this->setAdherent($adherent);
    }

    public function getAuthor(): ?Adherent
    {
        return $this->getAdherent();
    }
}

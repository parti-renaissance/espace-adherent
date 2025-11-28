<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\SubscriptionTypeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: '/v3',
    normalizationContext: ['groups' => ['subscription_type_read']],
    order: ['position' => 'ASC'],
    paginationEnabled: false,
)]
#[ORM\Entity(repositoryClass: SubscriptionTypeRepository::class)]
#[ORM\Index(columns: ['code'])]
#[ORM\Table]
#[UniqueEntity(fields: ['code'])]
class SubscriptionType
{
    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['profile_read', 'subscription_type_read'])]
    #[ORM\Column]
    private $label;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['profile_read', 'subscription_type_read', 'profile_update'])]
    #[ORM\Column(unique: true)]
    private $code;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 64)]
    #[ORM\Column(length: 64, unique: true, nullable: true)]
    private $externalId;

    /**
     * @var int
     */
    #[ORM\Column(type: 'smallint', options: ['unsigned' => true, 'default' => 0])]
    private $position = 0;

    public function __construct(?string $label = null, ?string $code = null, ?string $externalId = null)
    {
        $this->label = $label;
        $this->code = $code;
        $this->externalId = $externalId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId): void
    {
        $this->externalId = $externalId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(?int $position): void
    {
        $this->position = $position;
    }

    #[Groups(['subscription_type_read'])]
    public function getType(): string
    {
        if (str_contains($this->code, 'sms')) {
            return 'sms';
        }

        return 'email';
    }

    public function __toString(): string
    {
        return (string) $this->code;
    }
}

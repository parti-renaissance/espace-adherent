<?php

namespace App\Entity\Instance;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Instance\InstanceQualityScopeEnum;
use App\Repository\Instance\InstanceQualityRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: InstanceQualityRepository::class)]
class InstanceQuality
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var string
     */
    #[ORM\Column(unique: true)]
    private $code;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $label;

    /**
     * @var string[]
     */
    #[ORM\Column(type: 'simple_array')]
    private $scopes;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $custom;

    public function __construct(string $code, array $scopes, bool $custom = true, ?UuidInterface $uuid = null)
    {
        $this->code = $code;
        $this->scopes = $scopes;
        $this->custom = $custom;
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function equals(self $quality): bool
    {
        return $this->id === $quality->getId();
    }

    public function hasNationalCouncilScope(): bool
    {
        return \in_array(InstanceQualityScopeEnum::NATIONAL_COUNCIL, $this->scopes);
    }

    public function isCustom(): bool
    {
        return $this->custom;
    }

    public function getFullLabel(): string
    {
        return sprintf('%s [%s]', $this->__toString(), implode(', ', $this->scopes));
    }

    public function __toString(): string
    {
        return $this->label ?? $this->code;
    }
}

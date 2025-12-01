<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ScopeRepository;
use App\Scope\AppEnum;
use App\Scope\FeatureEnum;
use App\Scope\ScopeEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ScopeRepository::class)]
#[UniqueEntity(fields: ['code'])]
class Scope implements \Stringable
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
    #[Assert\Choice(choices: ScopeEnum::ALL)]
    #[Assert\NotBlank]
    #[ORM\Column(unique: true)]
    private $code;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 100)]
    private $name;

    /**
     * @var array
     */
    #[Assert\Choice(choices: FeatureEnum::ALL, multiple: true)]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $features;

    #[Assert\Choice(choices: FeatureEnum::ALL, multiple: true)]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    public ?array $canaryFeatures = null;

    /**
     * @var array
     */
    #[Assert\Choice(choices: AppEnum::ALL, multiple: true)]
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private $apps;

    #[ORM\Column(nullable: true)]
    public ?string $colorPrimary = null;
    #[ORM\Column(nullable: true)]
    public ?string $colorSoft = null;
    #[ORM\Column(nullable: true)]
    public ?string $colorHover = null;
    #[ORM\Column(nullable: true)]
    public ?string $colorActive = null;

    public function __construct(?string $code = null, ?string $name = null, ?array $features = null, ?array $apps = null)
    {
        $this->code = $code;
        $this->name = $name;
        $this->features = $features ?? [];
        $this->apps = $apps ?? [];
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getFeatures(): array
    {
        return $this->features;
    }

    public function setFeatures(array $features): void
    {
        $this->features = $features;
    }

    public function getApps(): array
    {
        return $this->apps;
    }

    public function setApps(array $apps): void
    {
        $this->apps = $apps;
    }

    public function getTheme(): array
    {
        return [
            'primary' => $this->colorPrimary,
            'soft' => $this->colorSoft,
            'hover' => $this->colorHover,
            'active' => $this->colorActive,
        ];
    }
}

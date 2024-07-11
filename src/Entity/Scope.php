<?php

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
class Scope
{
    /**
     * @var int|null
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * @var string|null
     */
    #[ORM\Column(unique: true)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: ScopeEnum::ALL)]
    private $code;

    /**
     * @var string|null
     */
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(max: '100')]
    private $name;

    /**
     * @var array
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    #[Assert\Choice(choices: FeatureEnum::ALL, multiple: true)]
    private $features;

    /**
     * @var array
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    #[Assert\Choice(choices: AppEnum::ALL, multiple: true)]
    private $apps;

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
}

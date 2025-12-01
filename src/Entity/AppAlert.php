<?php

declare(strict_types=1);

namespace App\Entity;

use App\JeMengage\Alert\AlertTypeEnum;
use App\Repository\AppAlertRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: AppAlertRepository::class)]
class AppAlert implements \Stringable, EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[NotBlank]
    #[ORM\Column(enumType: AlertTypeEnum::class)]
    public ?AlertTypeEnum $type = null;

    #[NotBlank]
    #[ORM\Column]
    public ?string $label = null;

    #[NotBlank]
    #[ORM\Column]
    public ?string $title = null;

    #[NotBlank]
    #[ORM\Column]
    public ?string $description = null;

    #[ORM\Column(nullable: true)]
    public ?string $ctaLabel = null;

    #[ORM\Column(nullable: true)]
    public ?string $ctaUrl = null;

    #[Assert\Url]
    #[ORM\Column(nullable: true)]
    public ?string $imageUrl = null;

    #[Assert\Url]
    #[ORM\Column(nullable: true)]
    public ?string $shareUrl = null;

    #[ORM\Column(type: 'json', nullable: true, options: ['jsonb' => true])]
    public ?array $data = null;

    #[NotBlank]
    #[ORM\Column(type: 'datetime')]
    public ?\DateTimeInterface $beginAt = null;

    #[NotBlank]
    #[ORM\Column(type: 'datetime')]
    public ?\DateTimeInterface $endAt = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $withMagicLink = false;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    public bool $isActive = true;

    public function __construct()
    {
        $this->uuid = Uuid::uuid4();
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }
}

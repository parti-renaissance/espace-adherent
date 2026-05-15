<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TallyFormRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TallyFormRepository::class)]
#[ORM\Table]
#[UniqueEntity(fields: ['slug'], message: 'Ce slug est déjà utilisé.')]
class TallyForm implements \Stringable, EntityAdministratorBlameableInterface
{
    use EntityAdministratorBlameableTrait;
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityUTMTrait;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column]
    private ?string $title = null;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[a-z0-9\-\/]+$/', message: 'Le slug ne peut contenir que des lettres minuscules, des chiffres, des tirets et des slashs.')]
    #[ORM\Column(unique: true)]
    private ?string $slug = null;

    #[Assert\Length(max: 50)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 50)]
    private ?string $tallyId = null;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private bool $published = true;

    public function __construct(?Uuid $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::v4();
        $this->utmSource = 'consultation';
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getTallyId(): ?string
    {
        return $this->tallyId;
    }

    public function setTallyId(?string $tallyId): void
    {
        $this->tallyId = $tallyId;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }
}

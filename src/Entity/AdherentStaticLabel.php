<?php

namespace App\Entity;

use App\Repository\AdherentStaticLabelRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AdherentStaticLabelRepository::class)]
#[UniqueEntity(fields: ['code'])]
#[UniqueEntity(fields: ['label'])]
class AdherentStaticLabel
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private ?int $id = null;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column(unique: true)]
    public ?string $code = null;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column(unique: true)]
    public ?string $label = null;

    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: AdherentStaticLabelCategory::class)]
    public ?AdherentStaticLabelCategory $category = null;

    public function __toString(): string
    {
        return \sprintf('%s (%s:%s)', $this->label, $this->category->code, $this->code);
    }
}

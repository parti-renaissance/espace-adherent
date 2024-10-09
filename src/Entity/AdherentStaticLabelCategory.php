<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['code'])]
#[UniqueEntity(fields: ['label'])]
class AdherentStaticLabelCategory
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

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $sync = false;

    public function __toString(): string
    {
        return \sprintf('%s (%s)', $this->label, $this->code);
    }
}

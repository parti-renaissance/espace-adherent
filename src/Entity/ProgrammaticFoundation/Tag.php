<?php

namespace App\Entity\ProgrammaticFoundation;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'programmatic_foundation_tag')]
#[UniqueEntity(fields: ['label'])]
class Tag
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[Groups(['approach_list_read'])]
    #[ORM\Column(length: 100, unique: true)]
    private $label;

    public function __construct(string $label = '')
    {
        $this->label = $label;
    }

    public function __toString(): string
    {
        return $this->label;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }
}

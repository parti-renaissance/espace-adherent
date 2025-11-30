<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'social_share_categories')]
class SocialShareCategory implements \Stringable
{
    use PositionTrait;

    #[ORM\Column(type: 'bigint')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 100)]
    private $name;

    #[Gedmo\Slug(fields: ['name'])]
    #[ORM\Column]
    private $slug;

    public function __construct(string $name = '', int $position = 1)
    {
        $this->name = $name;
        $this->position = $position;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = (string) $name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }
}

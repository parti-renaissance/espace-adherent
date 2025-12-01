<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProposalThemeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProposalThemeRepository::class)]
#[ORM\Table(name: 'proposals_themes')]
class ProposalTheme implements \Stringable
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var string
     */
    #[ORM\Column(length: 50)]
    private $name;

    /**
     * @var string
     */
    #[ORM\Column(length: 10)]
    private $color;

    public function __construct(string $name = '', string $color = '000000')
    {
        $this->name = $name;
        $this->color = $color;
    }

    public function __toString()
    {
        return $this->name ?: '';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;

        return $this;
    }
}

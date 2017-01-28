<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="proposals_themes")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProposalThemeRepository")
 */
class ProposalTheme
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     */
    private $color;

    public function __construct(string $name = '', string $color = '000000')
    {
        $this->name = $name;
        $this->color = $color;
    }

    public function __toString()
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

    public function setName(string $name): ProposalTheme
    {
        $this->name = $name;

        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): ProposalTheme
    {
        $this->color = $color;

        return $this;
    }
}

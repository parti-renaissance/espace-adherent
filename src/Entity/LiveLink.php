<?php

namespace App\Entity;

use App\Repository\LiveLinkRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LiveLinkRepository::class)]
#[ORM\Table(name: 'live_links')]
class LiveLink
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    /**
     * @var int|null
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'smallint')]
    private $position;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column]
    private $title;

    /**
     * @var string|null
     */
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column]
    private $link;

    /**
     * @var \DateTime
     */
    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(type: 'datetime')]
    private $updatedAt;

    public function __toString()
    {
        return $this->title ?: '';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setLink($link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}

<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="proposals")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProposalRepository")
 */
class Proposal
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
     * @var int
     *
     * @ORM\Column(type="smallint")
     *
     * @Assert\NotBlank
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     */
    private $link;

    /**
     * @var array
     *
     * @ORM\Column(type="simple_array")
     */
    private $tags;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    public function __construct()
    {
        $this->tags = [];
    }

    public function __toString()
    {
        return $this->title;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($position): Proposal
    {
        $this->position = $position;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title): Proposal
    {
        $this->title = $title;

        return $this;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setLink($link): Proposal
    {
        $this->link = $link;

        return $this;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(array $tags): Proposal
    {
        $this->tags = $tags;
        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): Proposal
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}


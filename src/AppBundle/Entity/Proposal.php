<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="proposals")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProposalRepository")
 */
class Proposal
{
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;
    use EntityContentTrait;

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
     * @var ProposalTheme[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ProposalTheme")
     */
    private $themes;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $published = false;

    /**
     * @var Media|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Media")
     *
     * @Assert\NotBlank
     */
    private $media;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $displayMedia = true;

    public function __construct()
    {
        $this->themes = new ArrayCollection();
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

    public function addTheme(ProposalTheme $theme): Proposal
    {
        $this->themes[] = $theme;

        return $this;
    }

    public function removeTheme(ProposalTheme $theme)
    {
        $this->themes->removeElement($theme);
    }

    /**
     * @return ProposalTheme[]|Collection
     */
    public function getThemes()
    {
        return $this->themes;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): Proposal
    {
        $this->published = $published;

        return $this;
    }

    /**
     * @return Media|null
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param Media|null $media
     *
     * @return Proposal
     */
    public function setMedia(Media $media = null): Proposal
    {
        $this->media = $media;

        return $this;
    }

    public function displayMedia(): bool
    {
        return $this->displayMedia;
    }

    public function setDisplayMedia(bool $displayMedia): Proposal
    {
        $this->displayMedia = $displayMedia;

        return $this;
    }
}

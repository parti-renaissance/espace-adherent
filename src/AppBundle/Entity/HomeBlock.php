<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="home_blocks")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\HomeBlockRepository")
 *
 * @UniqueEntity(fields={"position"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class HomeBlock
{
    const TYPE_ARTICLE = 'article';
    const TYPE_VIDEO = 'video';

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(length=20, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=20)
     */
    private $position;

    /**
     * @var string|null
     *
     * @ORM\Column(length=20, unique=true)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=20)
     */
    private $positionName;

    /**
     * @var string|null
     *
     * @ORM\Column(length=70)
     *
     * @Assert\NotBlank
     * @Assert\Length(max=70)
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(length=100, nullable=true)
     *
     * @Assert\Length(max=100)
     */
    private $subtitle;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     *
     * @Assert\NotBlank
     * @Assert\Choice({"video", "article"})
     */
    private $type = self::TYPE_ARTICLE;

    /**
     * @var Media|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Media")
     *
     * @Assert\NotBlank
     */
    private $media;

    /**
     * @var string|null
     *
     * @ORM\Column(length=255)
     *
     * @Assert\NotBlank
     */
    private $link;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Gedmo\Timestampable(on="update")
     */
    private $updatedAt;

    public function __toString()
    {
        return $this->positionName ?: '';
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param null|string $position
     *
     * @return HomeBlock
     */
    public function setPosition($position): HomeBlock
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPositionName()
    {
        return $this->positionName;
    }

    /**
     * @param null|string $positionName
     *
     * @return HomeBlock
     */
    public function setPositionName($positionName): HomeBlock
    {
        $this->positionName = $positionName;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     *
     * @return HomeBlock
     */
    public function setTitle($title): HomeBlock
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param null|string $subtitle
     *
     * @return HomeBlock
     */
    public function setSubtitle($subtitle): HomeBlock
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): HomeBlock
    {
        $this->type = $type;

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
     * @return HomeBlock
     */
    public function setMedia(Media $media = null): HomeBlock
    {
        $this->media = $media;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param null|string $link
     *
     * @return HomeBlock
     */
    public function setLink($link): HomeBlock
    {
        $this->link = $link;

        return $this;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): HomeBlock
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
}

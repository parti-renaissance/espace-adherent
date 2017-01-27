<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="articles")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArticleRepository")
 *
 * @UniqueEntity(fields={"slug"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Article
{
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;
    use EntityContentTrait;

    const CATEGORY_ACTUALITE = 'actualite';
    const CATEGORY_VIDEO = 'video';
    const CATEGORY_PHOTOS = 'photos';
    const CATEGORY_DISCOURS = 'discours';
    const CATEGORY_MEDIA = 'media';
    const CATEGORY_COMMUNIQUE = 'communique';

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(length=30)
     *
     * @Assert\Choice({"actualite", "video", "photos", "discours", "media", "communique"})
     */
    private $category = self::CATEGORY_ACTUALITE;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $published = false;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean")
     */
    private $displayMedia = true;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): Article
    {
        $this->category = $category;

        return $this;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): Article
    {
        $this->published = $published;

        return $this;
    }

    public function displayMedia(): bool
    {
        return $this->displayMedia;
    }

    public function setDisplayMedia(bool $displayMedia): self
    {
        $this->displayMedia = $displayMedia;

        return $this;
    }
}

<?php

namespace AppBundle\Entity\Mooc;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="mooc_chapter",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="mooc_chapter_slug", columns="slug")}
 * )
 *
 * @UniqueEntity(fields={"slug", "mooc"})
 *
 * @Algolia\Index(autoIndex=false)
 */
class Chapter
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $title;

    /**
     * @ORM\Column
     * @Gedmo\Slug(fields={"title"}, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="boolean")
     */
    private $published;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     */
    private $publishedAt;

    /**
     * @ORM\Column(type="smallint")
     * @Gedmo\SortablePosition
     */
    private $displayOrder;

    /**
     * @var Mooc
     *
     * @ORM\ManyToOne(targetEntity="Mooc", inversedBy="chapters")
     * @Gedmo\SortableGroup
     */
    private $mooc;

    /**
     * @var Chapter[]|Collection
     *
     * @ORM\OneToMany(targetEntity="Video", mappedBy="chapter", cascade={"all"})
     * @ORM\OrderBy({"displayOrder": "ASC"})
     *
     * @Assert\Valid
     */
    private $videos;

    public function __construct(string $title = null, bool $published = false, \DateTime $publishedAt = null)
    {
        $this->title = $title;
        $this->published = $published;
        $this->publishedAt = $publishedAt;
        $this->videos = new ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function isPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTime $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    public function getMooc(): ?Mooc
    {
        return $this->mooc;
    }

    public function setMooc(Mooc $mooc): void
    {
        $this->mooc = $mooc;
    }

    public function detachMooc(): void
    {
        $this->mooc = null;
    }

    public function getDisplayOrder(): ?int
    {
        return $this->displayOrder;
    }

    public function setDisplayOrder(int $displayOrder): void
    {
        $this->displayOrder = $displayOrder;
    }

    /**
     * @return Video[]|Collection|iterable
     */
    public function getVideos(): iterable
    {
        return $this->videos;
    }

    public function addVideo(Video $video): void
    {
        if (!$this->videos->contains($video)) {
            $video->setChapter($this);
            $this->videos->add($video);
        }
    }

    public function removeVideo(Video $video): void
    {
        $video->detachChapter();
        $this->videos->removeElement($video);
    }
}

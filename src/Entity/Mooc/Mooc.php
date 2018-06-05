<?php

namespace AppBundle\Entity\Mooc;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="mooc_slug", columns="slug")
 *     }
 * )
 *
 * @UniqueEntity("title")
 *
 * @Algolia\Index(autoIndex=false)
 */
class Mooc
{
    use EntityTimestampableTrait;

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
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(min=2, max=400)
     */
    private $description;

    /**
     * @ORM\Column
     * @Gedmo\Slug(fields={"title"}, unique=true)
     */
    private $slug;

    /**
     * @var Chapter[]|Collection
     *
     * @ORM\OneToMany(targetEntity="Chapter", mappedBy="mooc", cascade={"all"})
     * @ORM\OrderBy({"displayOrder": "ASC"})
     *
     * @Assert\Valid
     */
    private $chapters;

    public function __construct(string $title = null, string $description = null)
    {
        $this->title = $title;
        $this->description = $description;
        $this->chapters = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return Chapter[]|Collection|iterable
     */
    public function getChapters(): iterable
    {
        return $this->chapters;
    }

    public function addChapter(Chapter $chapter): void
    {
        if (!$this->chapters->contains($chapter)) {
            $chapter->setMooc($this);
            $this->chapters->add($chapter);
        }
    }

    public function removeChapter(Chapter $chapter): void
    {
        $chapter->detachMooc();
        $this->chapters->removeElement($chapter);
    }
}

<?php

declare(strict_types=1);

namespace App\Entity\Mooc;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Runroom\SortableBehaviorBundle\Behaviors\Sortable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'mooc_chapter')]
#[UniqueEntity(fields: ['slug', 'mooc'])]
class Chapter implements \Stringable
{
    use Sortable;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column]
    private $title;

    #[Gedmo\Slug(fields: ['title'], unique: true)]
    #[ORM\Column(unique: true)]
    private $slug;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $published;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime')]
    private $publishedAt;

    /**
     * @var Mooc
     */
    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne(targetEntity: Mooc::class, inversedBy: 'chapters')]
    private $mooc;

    /**
     * @var BaseMoocElement[]|Collection
     */
    #[Assert\Valid]
    #[ORM\OneToMany(mappedBy: 'chapter', targetEntity: BaseMoocElement::class, cascade: ['all'])]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private $elements;

    public function __construct(?string $title = null, bool $published = false, ?\DateTime $publishedAt = null)
    {
        $this->title = $title;
        $this->published = $published;
        $this->publishedAt = $publishedAt;
        $this->elements = new ArrayCollection();
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

    /**
     * @return BaseMoocElement[]|Collection
     */
    public function getElements(): Collection
    {
        return $this->elements;
    }

    public function addElement(BaseMoocElement $element): void
    {
        if (!$this->elements->contains($element)) {
            $element->setChapter($this);
            $this->elements->add($element);
        }
    }

    public function removeElement(BaseMoocElement $element): void
    {
        $element->detachChapter();
        $this->elements->removeElement($element);
    }
}

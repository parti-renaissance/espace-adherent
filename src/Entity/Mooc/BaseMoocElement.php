<?php

namespace AppBundle\Entity\Mooc;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use AppBundle\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="mooc_elements",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="mooc_element_slug", columns={"slug", "chapter_id"})}
 * )
 *
 * @UniqueEntity(fields={"slug", "chapter"})
 *
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     BaseMoocElement::ELEMENT_TYPE_VIDEO: "AppBundle\Entity\Mooc\Video",
 *     BaseMoocElement::ELEMENT_TYPE_QUIZ: "AppBundle\Entity\Mooc\Quizz",
 * })
 *
 * @Algolia\Index(autoIndex=false)
 */
abstract class BaseMoocElement
{
    use EntityTimestampableTrait;

    public const ELEMENT_TYPE_VIDEO = 'video';
    public const ELEMENT_TYPE_QUIZ = 'quiz';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    protected $title;

    /**
     * @ORM\Column
     * @Gedmo\Slug(fields={"title"}, unique=true)
     */
    protected $slug;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     * @Gedmo\SortablePosition
     */
    protected $position;

    /**
     * @var string
     *
     * @ORM\Column(length=800, nullable=true)
     *
     * @Assert\Length(min=5, max=800)
     */
    protected $content;

    /**
     * @var Chapter
     *
     * @ORM\ManyToOne(targetEntity="Chapter", inversedBy="elements", cascade={"persist"})
     * @Gedmo\SortableGroup
     *
     * @Assert\Valid
     */
    protected $chapter;

    /**
     * @var Collection|AttachmentLink[]
     *
     * @ORM\ManyToMany(targetEntity="AttachmentLink", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinTable(name="mooc_element_attachment_link")
     *
     * @Assert\Valid
     */
    protected $links;

    /**
     * @var Collection|AttachmentFile[]
     *
     * @ORM\ManyToMany(targetEntity="AttachmentFile", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinTable(name="mooc_element_attachment_file")
     *
     * @Assert\Valid
     */
    protected $files;

    public function __construct(string $title = null, string $content = null)
    {
        $this->title = $title;
        $this->content = $content;
        $this->links = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->title ?? 'New element';
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

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getChapter(): ?Chapter
    {
        return $this->chapter;
    }

    public function setChapter(Chapter $chapter): void
    {
        $this->chapter = $chapter;
    }

    public function detachChapter(): void
    {
        $this->chapter = null;
    }

    /**
     * @return Collection|AttachmentLink[]
     */
    public function getLinks(): Collection
    {
        return $this->links;
    }

    public function addLink(AttachmentLink $link): void
    {
        if (!$this->links->contains($link)) {
            $this->links->add($link);
        }
    }

    public function removeLink(AttachmentLink $link): void
    {
        $this->links->removeElement($link);
    }

    /**
     * @return Collection|AttachmentFile[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(AttachmentFile $file): void
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setType($this->getType());
        }
    }

    public function removeFile(AttachmentFile $file): void
    {
        $this->files->removeElement($file);
    }

    abstract public function getType(): string;

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}

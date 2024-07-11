<?php

namespace App\Entity\Mooc;

use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Runroom\SortableBehaviorBundle\Behaviors\Sortable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(fields={"slug", "chapter"})
 */
#[ORM\Entity]
#[ORM\Table(name: 'mooc_elements')]
#[ORM\UniqueConstraint(name: 'mooc_element_slug', columns: ['slug', 'chapter_id'])]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([MoocElementTypeEnum::VIDEO => MoocVideoElement::class, MoocElementTypeEnum::QUIZ => MoocQuizElement::class, MoocElementTypeEnum::IMAGE => MoocImageElement::class])]
#[UniqueEntity(fields: ['slug', 'chapter'])]
abstract class BaseMoocElement
{
    use EntityTimestampableTrait;
    use Sortable;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    protected $title;

    #[ORM\Column]
    #[Gedmo\Slug(fields: ['title'], unique: true)]
    protected $slug;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'text', nullable: true)]
    protected $content;

    /**
     * @var Chapter
     */
    #[ORM\ManyToOne(targetEntity: Chapter::class, cascade: ['persist'], inversedBy: 'elements')]
    #[Assert\Valid]
    #[Gedmo\SortableGroup]
    protected $chapter;

    /**
     * @var Collection|AttachmentLink[]
     */
    #[ORM\JoinTable(name: 'mooc_element_attachment_link')]
    #[ORM\ManyToMany(targetEntity: AttachmentLink::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    #[Assert\Valid]
    protected $links;

    /**
     * @var Collection|AttachmentFile[]
     */
    #[ORM\JoinTable(name: 'mooc_element_attachment_file')]
    #[ORM\ManyToMany(targetEntity: AttachmentFile::class, cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    #[Assert\Valid]
    protected $files;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private $shareTwitterText;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private $shareFacebookText;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private $shareEmailSubject;

    #[ORM\Column(length: 500)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 500, options: ['allowEmptyString' => true])]
    protected $shareEmailBody;

    public function __construct(
        ?string $title = null,
        ?string $content = null,
        ?string $shareTwitterText = null,
        ?string $shareFacebookText = null,
        ?string $shareEmailSubject = null,
        ?string $shareEmailBody = null
    ) {
        $this->title = $title;
        $this->content = $content;
        $this->links = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->shareTwitterText = $shareTwitterText;
        $this->shareFacebookText = $shareFacebookText;
        $this->shareEmailSubject = $shareEmailSubject;
        $this->shareEmailBody = $shareEmailBody;
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
        }
    }

    public function removeFile(AttachmentFile $file): void
    {
        $this->files->removeElement($file);
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getShareTwitterText(): ?string
    {
        return $this->shareTwitterText;
    }

    public function setShareTwitterText(string $shareTwitterText): void
    {
        $this->shareTwitterText = $shareTwitterText;
    }

    public function getShareFacebookText(): ?string
    {
        return $this->shareFacebookText;
    }

    public function setShareFacebookText(string $shareFacebookText): void
    {
        $this->shareFacebookText = $shareFacebookText;
    }

    public function getShareEmailSubject(): ?string
    {
        return $this->shareEmailSubject;
    }

    public function setShareEmailSubject(string $shareEmailSubject): void
    {
        $this->shareEmailSubject = $shareEmailSubject;
    }

    public function getShareEmailBody(): ?string
    {
        return $this->shareEmailBody;
    }

    public function setShareEmailBody(string $shareEmailBody): void
    {
        $this->shareEmailBody = $shareEmailBody;
    }

    abstract public function getType(): string;
}

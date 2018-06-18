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
 *     "video": "AppBundle\Entity\Mooc\Video",
 *     "quiz": "AppBundle\Entity\Mooc\Quiz",
 * })
 *
 * @Algolia\Index(autoIndex=false)
 */
abstract class BaseMoocElement
{
    use EntityTimestampableTrait;

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
     * @ORM\OrderBy({"id": "ASC"})
     *
     * @Assert\Valid
     */
    protected $links;

    /**
     * @var Collection|AttachmentFile[]
     *
     * @ORM\ManyToMany(targetEntity="AttachmentFile", cascade={"persist"}, orphanRemoval=true)
     * @ORM\JoinTable(name="mooc_element_attachment_file")
     * @ORM\OrderBy({"id": "ASC"})
     *
     * @Assert\Valid
     */
    protected $files;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $twitterText;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $facebookText;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $emailObject;

    /**
     * @ORM\Column(length=800)
     *
     * @Assert\Length(min=5, max=800)
     */
    protected $emailBody;

    public function __construct(
        string $title = null,
        string $content = null,
        string $twitterText = null,
        string $facebookText = null,
        string $emailObject = null,
        string $emailBody = null
    ) {
        $this->title = $title;
        $this->content = $content;
        $this->links = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->twitterText = $twitterText;
        $this->facebookText = $facebookText;
        $this->emailObject = $emailObject;
        $this->emailBody = $emailBody;
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

    public function getTwitterText(): ?string
    {
        return $this->twitterText;
    }

    public function setTwitterText(string $twitterText): void
    {
        $this->twitterText = $twitterText;
    }

    public function getFacebookText(): ?string
    {
        return $this->facebookText;
    }

    public function setFacebookText(string $facebookText): void
    {
        $this->facebookText = $facebookText;
    }

    public function getEmailObject(): ?string
    {
        return $this->emailObject;
    }

    public function setEmailObject(string $emailObject): void
    {
        $this->emailObject = $emailObject;
    }

    public function getEmailBody(): ?string
    {
        return $this->emailBody;
    }

    public function setEmailBody(string $emailBody): void
    {
        $this->emailBody = $emailBody;
    }
}

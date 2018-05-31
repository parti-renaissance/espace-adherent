<?php

namespace AppBundle\Entity\Mooc;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="mooc_chapter",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="mooc_chapter_video_slug", columns="slug"),
 *         @ORM\UniqueConstraint(name="mooc_chapter_video_order_display_by_chapter", columns={"display_order", "chapter_id"})
 *     }
 * )
 *
 * @UniqueEntity(
 *     fields={"displayOrder", "chapter"},
 * )
 *
 * @Algolia\Index(autoIndex=false)
 */
class Video extends BaseElement
{
    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Regex(pattern="/^[A-Za-z0-9_-]+$/", message="mooc.video.youtubeid_syntax")
     * @Assert\Length(min=2, max=11)
     */
    private $youtubeId;

    /**
     * @ORM\Column(length=800, nullable=true)
     *
     * @Assert\Length(min=5, max=800)
     */
    private $content;

    /**
     * @var Chapter
     *
     * @ORM\ManyToOne(targetEntity="Chapter", inversedBy="videos")
     */
    private $chapter;

    /**
     * @var AttachmentLink[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="AttachmentLink", cascade={"all"})
     * @ORM\JoinTable(
     *     name="mooc_video_attachment_link",
     *     joinColumns={
     *         @ORM\JoinColumn(name="video_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="attachment_link_id", referencedColumnName="id", unique=true)
     *     }
     * )
     */
    private $attachmentLinks;

    public function __construct(
        string $title = null,
        string $youtubeId = null,
        string $content = null,
        int $displayOrder = null
    ) {
        $this->title = $title;
        $this->youtubeId = $youtubeId;
        $this->content = $content;
        $this->displayOrder = $displayOrder;
        $this->attachmentLinks = new ArrayCollection();
    }

    public function getYoutubeId(): ?string
    {
        return $this->youtubeId;
    }

    public function setYoutubeId(string $youtubeId): void
    {
        $this->youtubeId = $youtubeId;
    }

    public function hasYoutubeThumbnail(): bool
    {
        return null !== $this->youtubeId;
    }

    public function getYoutubeThumbnail(): ?string
    {
        return sprintf('https://img.youtube.com/vi/%s/0.jpg', $this->youtubeId);
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
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
     * @return AttachmentLink[]|Collection|iterable
     */
    public function getAttachmentLinks(): iterable
    {
        return $this->attachmentLinks;
    }

    public function addAttachmentLink(AttachmentLink $attachmentLink): void
    {
        if (!$this->attachmentLinks->contains($attachmentLink)) {
            $this->attachmentLinks->add($attachmentLink);
        }
    }

    public function removeAttachmentLink(AttachmentLink $attachmentLink): void
    {
        $this->attachmentLinks->removeElement($attachmentLink);
    }
}

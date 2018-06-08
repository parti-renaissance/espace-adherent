<?php

namespace AppBundle\Entity\Mooc;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 *
 * @Algolia\Index(autoIndex=false)
 */
class Video extends BaseMoocElement
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
     * @ORM\Column(type="time")
     *
     * @Assert\Time
     */
    private $duration;

    public function __construct(
        string $title = null,
        string $description = null,
        string $youtubeId = null,
        \DateTime $duration = null
    ) {
        parent::__construct($title, $description);
        $this->youtubeId = $youtubeId;
        $this->duration = $duration;
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

    public function getDuration(): ?\DateTime
    {
        return $this->duration;
    }

    public function setDuration(\DateTime $duration): void
    {
        $this->duration = $duration;
    }

    public function getType(): string
    {
        return parent::ELEMENT_TYPE_VIDEO;
    }
}

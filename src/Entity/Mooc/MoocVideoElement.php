<?php

declare(strict_types=1);

namespace App\Entity\Mooc;

use Cake\Chronos\Chronos;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class MoocVideoElement extends BaseMoocElement
{
    #[Assert\Length(min: 2, max: 11)]
    #[Assert\Regex(pattern: '/^[A-Za-z0-9_-]+$/', message: 'mooc.youtubeid_syntax')]
    #[ORM\Column(nullable: true)]
    private $youtubeId;

    #[Assert\Time]
    #[ORM\Column(type: 'time', nullable: true)]
    private $duration;

    public function __construct(
        ?string $title = null,
        ?string $content = null,
        ?string $shareTwitterText = null,
        ?string $shareFacebokText = null,
        ?string $shareEmailObject = null,
        ?string $shareEmailBody = null,
        ?string $youtubeId = null,
        ?\DateTimeInterface $duration = null,
    ) {
        parent::__construct($title, $content, $shareTwitterText, $shareFacebokText, $shareEmailObject, $shareEmailBody);
        $this->youtubeId = $youtubeId;
        $this->duration = $duration ?? Chronos::create();
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
        return \sprintf('https://img.youtube.com/vi/%s/0.jpg', $this->youtubeId);
    }

    public function getDuration(): \DateTime
    {
        return $this->duration;
    }

    public function setDuration(\DateTime $duration): void
    {
        $this->duration = $duration;
    }

    public function getType(): string
    {
        return MoocElementTypeEnum::VIDEO;
    }
}

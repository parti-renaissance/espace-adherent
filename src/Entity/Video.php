<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Repository\VideoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/videos/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
        ),
    ],
    normalizationContext: ['groups' => ['video_read']],
)]
#[ORM\Entity(repositoryClass: VideoRepository::class)]
#[ORM\Index(fields: ['status'])]
class Video implements \Stringable, EntityAdministratorBlameableInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    public const string FILE_NAME_HLS = 'master.m3u8';
    public const string FILE_NAME_PREVIEW = 'preview.mp4';
    public const string FILE_NAME_THUMBNAIL = 'thumbnail0000000000.jpeg';

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['video_read'])]
    #[ORM\Column]
    public ?string $title = null;

    #[ORM\Column(length: 32, enumType: VideoStatusEnum::class)]
    public VideoStatusEnum $status = VideoStatusEnum::PENDING;

    #[Assert\Length(max: 255)]
    #[ORM\Column(nullable: true)]
    public ?string $mediaPath = null;

    #[Assert\PositiveOrZero]
    #[Groups(['video_read'])]
    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    public ?int $duration = null;

    #[Assert\Positive]
    #[Groups(['video_read'])]
    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    public ?int $width = null;

    #[Assert\Positive]
    #[Groups(['video_read'])]
    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    public ?int $height = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $sourceUri = null;

    // Path of our durable copy of the source inside GCLOUD_BUCKET. Transcoder input and relaunch source.
    #[ORM\Column(nullable: true)]
    public ?string $originalPath = null;

    // Resource name of the current GCP Transcoder job.
    #[ORM\Column(nullable: true)]
    public ?string $transcodingJobName = null;

    // Transcoder error message, set when status is FAILED.
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $failureReason = null;

    #[ORM\Column(options: ['default' => false])]
    public bool $transcodeWithoutAudio = false;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $transcodingStartedAt = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $transcodingFinishedAt = null;

    public function __construct(?Uuid $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::v4();
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }
}

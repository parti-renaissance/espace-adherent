<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
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
#[ORM\Entity]
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

    public function __construct(?Uuid $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::v4();
    }

    public function __toString(): string
    {
        return (string) $this->title;
    }
}

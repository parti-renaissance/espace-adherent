<?php

declare(strict_types=1);

namespace App\Entity\SocialNetwork;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\SocialNetworkFeedRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SocialNetworkFeedRepository::class)]
class SocialNetworkFeed
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * Internal identifier from the external scraping tool. Used as the upsert key.
     */
    #[ORM\Column(type: 'integer', unique: true, options: ['unsigned' => true])]
    public int $scraperId;

    /**
     * Identifier of the post on the social platform.
     */
    #[ORM\Column]
    public string $postId;

    #[ORM\Column]
    public string $platform;

    #[ORM\Column(nullable: true)]
    public ?string $username = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $description = null;

    #[ORM\Column(type: 'datetime_immutable', nullable: true)]
    public ?\DateTimeImmutable $datePublished = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $imageUrl = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $avatarImageUrl = null;

    /**
     * Path of the main image copied to our public bucket, relative to the public storage.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $publicImagePath = null;

    /**
     * Path of the avatar image copied to our public bucket, relative to the public storage.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $publicAvatarImagePath = null;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    public ?int $imageWidth = null;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    public ?int $imageHeight = null;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    public ?int $avatarWidth = null;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    public ?int $avatarHeight = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $url = null;

    #[ORM\Column(nullable: true)]
    public ?int $score = null;

    /**
     * Raw payload as received from the scraper. Everything is kept.
     */
    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $rawJson = null;

    /**
     * @var Collection<int, SocialNetworkFeedVideo>
     */
    #[ORM\OneToMany(targetEntity: SocialNetworkFeedVideo::class, mappedBy: 'feed', cascade: ['persist'], orphanRemoval: true)]
    public Collection $videos;

    /**
     * @var Collection<int, SocialNetworkFeedPhoto>
     */
    #[ORM\OneToMany(targetEntity: SocialNetworkFeedPhoto::class, mappedBy: 'feed', cascade: ['persist'], orphanRemoval: true)]
    public Collection $photos;

    public function __construct()
    {
        $this->uuid = Uuid::v4();
        $this->videos = new ArrayCollection();
        $this->photos = new ArrayCollection();
    }

    public function addVideo(SocialNetworkFeedVideo $video): void
    {
        if (!$this->videos->contains($video)) {
            $this->videos->add($video);
        }
    }

    public function clearVideos(): void
    {
        $this->videos->clear();
    }

    public function addPhoto(SocialNetworkFeedPhoto $photo): void
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
        }
    }

    public function clearPhotos(): void
    {
        $this->photos->clear();
    }
}

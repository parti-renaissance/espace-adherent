<?php

declare(strict_types=1);

namespace App\Entity\SocialNetwork;

use App\Entity\Video;
use App\Repository\SocialNetworkFeedVideoRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SocialNetworkFeedVideoRepository::class)]
class SocialNetworkFeedVideo
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    public ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: SocialNetworkFeed::class, inversedBy: 'videos')]
    public SocialNetworkFeed $feed;

    /**
     * Internal identifier of the video from the external scraping tool.
     */
    #[ORM\Column(name: 'scraper_id', type: 'integer', nullable: true, options: ['unsigned' => true])]
    public ?int $scraperId = null;

    #[ORM\Column(nullable: true)]
    public ?string $videoType = null;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    public ?int $width = null;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    public ?int $height = null;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    public ?int $bitrate = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $streamUrl = null;

    #[ORM\OneToOne]
    public ?Video $video = null;

    public function __construct(SocialNetworkFeed $feed)
    {
        $this->feed = $feed;
    }
}

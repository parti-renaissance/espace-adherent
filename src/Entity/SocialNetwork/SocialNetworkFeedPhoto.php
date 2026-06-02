<?php

declare(strict_types=1);

namespace App\Entity\SocialNetwork;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class SocialNetworkFeedPhoto
{
    #[ORM\Column(type: 'integer', options: ['unsigned' => true])]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    public ?int $id = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: SocialNetworkFeed::class, inversedBy: 'photos')]
    public SocialNetworkFeed $feed;

    /**
     * Internal identifier of the photo from the external scraping tool.
     */
    #[ORM\Column(type: 'integer', nullable: true, options: ['unsigned' => true])]
    public ?int $scraperId = null;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    public ?int $width = null;

    #[ORM\Column(nullable: true, options: ['unsigned' => true])]
    public ?int $height = null;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $src = null;

    /**
     * Path of this photo copied to our public bucket, relative to the public storage.
     */
    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $publicSrc = null;

    public function __construct(SocialNetworkFeed $feed)
    {
        $this->feed = $feed;
    }
}

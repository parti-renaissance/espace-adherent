<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FacebookVideoRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FacebookVideoRepository::class)]
#[ORM\Table(name: 'facebook_videos')]
class FacebookVideo
{
    use EntityTimestampableTrait;

    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    #[ORM\Id]
    private $id;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    #[ORM\Column]
    private $facebookUrl;

    #[Assert\Length(max: 255)]
    #[Assert\Url]
    #[ORM\Column(nullable: true)]
    private $twitterUrl;

    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[ORM\Column]
    private $description;

    #[Assert\Length(max: 100)]
    #[Assert\NotBlank]
    #[ORM\Column(length: 100)]
    private $author;

    #[ORM\Column(type: 'integer')]
    private $position = 1;

    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    private $published = false;

    public function __construct()
    {
        $this->published = false;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFacebookUrl(): ?string
    {
        return $this->facebookUrl;
    }

    public function setFacebookUrl(?string $facebookUrl)
    {
        $this->facebookUrl = $facebookUrl;
    }

    public function getTwitterUrl(): ?string
    {
        return $this->twitterUrl;
    }

    public function setTwitterUrl(?string $twitterUrl)
    {
        $this->twitterUrl = $twitterUrl;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description)
    {
        $this->description = $description;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author)
    {
        $this->author = $author;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position)
    {
        $this->position = $position;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published)
    {
        $this->published = $published;

        return $this;
    }
}

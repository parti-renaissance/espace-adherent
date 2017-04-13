<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="facebook_videos")
 */
class FacebookVideo
{
    use EntityTimestampableTrait;
    use EntityPublishableTrait;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     * @Assert\Url
     */
    private $url;

    /**
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank
     */
    private $description;

    /**
     * @ORM\Column(length=100)
     *
     * @Assert\NotBlank
     */
    private $author;

    /**
     * @ORM\Column(type="integer")
     */
    private $position;

    public function __construct()
    {
        $this->position = 1;
        $this->published = false;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(string $author)
    {
        $this->author = $author;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position)
    {
        $this->position = $position;
    }
}

<?php

namespace AppBundle\Committee\Feed;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use Symfony\Component\Validator\Constraints as Assert;

class CommitteeMessage
{
    private $author;
    private $committee;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=10, minMessage="committee.message.min_length")
     */
    private $content;

    private $published;
    private $createdAt;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=80)
     */
    private $subject;

    public function __construct(
        Adherent $author,
        Committee $committee,
        string $subject = null,
        string $content = null,
        bool $published = false,
        string $createdAt = 'now'
    ) {
        $this->author = $author;
        $this->committee = $committee;
        $this->subject = $subject;
        $this->content = $content;
        $this->published = $published;
        $this->createdAt = new \DateTime($createdAt);
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getAuthor(): Adherent
    {
        return $this->author;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }
}

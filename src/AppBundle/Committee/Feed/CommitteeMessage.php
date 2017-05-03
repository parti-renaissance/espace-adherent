<?php

namespace AppBundle\Committee\Feed;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use Symfony\Component\Validator\Constraints as Assert;

class CommitteeMessage
{
    /**
     * @Assert\NotBlank
     * @Assert\Length(min=10, minMessage="committee.message.min_length")
     */
    private $content;

    private $author;
    private $committee;
    private $createdAt;

    public function __construct(Adherent $author, Committee $committee, string $content = null, string $createdAt = 'now')
    {
        $this->author = $author;
        $this->committee = $committee;
        $this->content = $content;
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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }
}

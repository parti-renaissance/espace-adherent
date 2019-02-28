<?php

namespace AppBundle\Committee\Feed;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Validator\WysiwygLength as AssertWysiwygLength;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Assert\Expression(
 *     expression="this.isPublished() != false || this.isSendNotification() != false",
 *     message="Vous devez cocher au moins une des deux cases"
 * )
 */
class CommitteeMessage
{
    private $author;
    private $committee;

    /**
     * @Assert\NotBlank
     * @AssertWysiwygLength(
     *     min=10,
     *     max=6000,
     *     minMessage="committee.message.min_length",
     *     maxMessage="committee.message.max_length"
     * )
     */
    private $content;
    private $published;
    private $sendNotification;
    private $createdAt;

    /**
     * @Assert\NotBlank(groups={"notification"})
     * @Assert\Length(max=80, groups={"notification"})
     */
    private $subject;

    public function __construct(
        Adherent $author,
        Committee $committee,
        string $subject = null,
        string $content = null,
        bool $published = false,
        string $createdAt = 'now',
        bool $sendNotification = true
    ) {
        $this->author = $author;
        $this->committee = $committee;
        $this->subject = $subject;
        $this->content = $content;
        $this->published = $published;
        $this->sendNotification = $sendNotification;
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

    public function isSendNotification(): bool
    {
        return $this->sendNotification;
    }

    public function setSendNotification(bool $sendNotification): void
    {
        $this->sendNotification = $sendNotification;
    }
}

<?php

namespace App\Committee;

use App\Entity\Adherent;
use Symfony\Component\Validator\Constraints as Assert;

class CommitteeContactMembersCommand
{
    /** @var Adherent[] */
    private $recipients;

    /** @var Adherent */
    private $sender;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=80)
     */
    private $subject;

    /**
     * @Assert\NotBlank
     * @Assert\Length(min=10, minMessage="committee.message.min_length")
     */
    private $message;

    public function __construct(array $recipients, Adherent $sender, string $message = null)
    {
        $this->recipients = $recipients;
        $this->sender = $sender;
        $this->message = $message;
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function getSender(): Adherent
    {
        return $this->sender;
    }

    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}

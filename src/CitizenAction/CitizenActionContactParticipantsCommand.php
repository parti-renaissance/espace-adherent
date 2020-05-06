<?php

namespace App\CitizenAction;

use App\Entity\Adherent;
use Symfony\Component\Validator\Constraints as Assert;

class CitizenActionContactParticipantsCommand
{
    /**
     * @var Adherent[]
     */
    private $recipients;

    private $sender;

    /**
     * @Assert\NotBlank
     */
    private $message;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max=80)
     */
    private $subject;

    public function __construct(Adherent $sender, array $recipients, string $subject = null, string $message = null)
    {
        $this->sender = $sender;
        $this->recipients = $recipients;
        $this->subject = $subject;
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

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setSubject(?string $subject): void
    {
        $this->subject = $subject;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }
}

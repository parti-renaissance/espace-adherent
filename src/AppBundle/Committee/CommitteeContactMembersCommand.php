<?php

namespace AppBundle\Committee;

use AppBundle\Entity\Adherent;
use AppBundle\Utils\HtmlPurifier;
use Symfony\Component\Validator\Constraints as Assert;

class CommitteeContactMembersCommand
{
    /** @var Adherent[] */
    private $recipients;

    /** @var Adherent */
    private $sender;

    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=10,
     *     max=1500,
     *     minMessage="committee.message.min_length",
     *     maxMessage="committee.message.max_length",
     * )
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

    public function setMessage(?string $message)
    {
        $this->message = HtmlPurifier::purify($message);
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }
}

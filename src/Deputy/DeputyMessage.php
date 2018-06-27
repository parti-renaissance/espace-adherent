<?php

namespace AppBundle\Deputy;

use AppBundle\Entity\Adherent;
use Symfony\Component\Validator\Constraints as Assert;

class DeputyMessage
{
    /**
     * @var Adherent
     */
    private $from;

    /**
     * @var Adherent[]
     */
    private $recipients;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     */
    private $subject;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="message.not_blank")
     * @Assert\Length(
     *     min=10,
     *     max=5000,
     *     minMessage="message.min_length",
     *     maxMessage="message.max_length",
     * )
     */
    private $content;

    public function __construct(Adherent $from, array $recipients)
    {
        $this->from = $from;
        $this->recipients = $recipients;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getFrom(): Adherent
    {
        return $this->from;
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }
}

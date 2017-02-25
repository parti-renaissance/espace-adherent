<?php

namespace AppBundle\Contact;

use AppBundle\Entity\Adherent;
use Symfony\Component\Validator\Constraints as Assert;

class ContactMessage
{
    /**
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=10,
     *     max=1500,
     *     minMessage="adherent.contact.min_length",
     *     maxMessage="adherent.contact.max_length",
     * )
     */
    private $content;

    private $from;
    private $to;

    public function __construct(Adherent $from, Adherent $to, string $content = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->content = $content;
    }

    public function getFrom(): Adherent
    {
        return $this->from;
    }

    public function getTo(): Adherent
    {
        return $this->to;
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

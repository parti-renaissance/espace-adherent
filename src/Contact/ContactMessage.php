<?php

namespace AppBundle\Contact;

use AppBundle\Entity\Adherent;
use AppBundle\Validator\Recaptcha as AssertRecaptcha;
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

    /**
     * @var string
     *
     * @Assert\NotBlank(message="common.recaptcha.invalid_message")
     * @AssertRecaptcha
     */
    public $recaptcha = '';

    public function __construct(Adherent $from, Adherent $to, string $content = null)
    {
        $this->from = $from;
        $this->to = $to;
        $this->content = $content;
    }

    public static function createWithCaptcha(
        string $recaptcha,
        Adherent $from,
        Adherent $to,
        string $content = null
    ): self {
        $message = new self($from, $to, $content);
        $message->recaptcha = $recaptcha;

        return $message;
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

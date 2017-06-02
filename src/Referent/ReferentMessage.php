<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;
use Symfony\Component\Validator\Constraints as Assert;

class ReferentMessage
{
    /**
     * @var Adherent
     */
    private $from;

    /**
     * @var ManagedUser[]
     */
    private $to;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     */
    private $subject;

    /**
     * @var string|null
     *
     * @Assert\NotBlank
     * @Assert\Length(
     *     min=10,
     *     max=10000,
     *     minMessage="referent.message.min_length",
     *     maxMessage="referent.message.max_length",
     * )
     */
    private $content;

    public function __construct(Adherent $from, array $to)
    {
        $this->to = $to;
        $this->from = $from;
    }

    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function getFrom(): Adherent
    {
        return $this->from;
    }

    /**
     * @return ManagedUser[]
     */
    public function getTo(): array
    {
        return $this->to;
    }

    public function countAdherents(): int
    {
        return count(array_filter($this->to, function (ManagedUser $item) {
            return $item->getType() === ManagedUser::TYPE_ADHERENT;
        }));
    }

    public function countNewsletterSubscribers(): int
    {
        return count(array_filter($this->to, function (ManagedUser $item) {
            return $item->getType() === ManagedUser::TYPE_NEWSLETTER_SUBSCRIBER;
        }));
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

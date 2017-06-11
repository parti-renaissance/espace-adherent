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
     * @var ManagedUsersFilter
     */
    private $filter;

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

    public function __construct(Adherent $from, ManagedUsersFilter $filter)
    {
        $this->filter = $filter;
        $this->from = $from;
    }

    public static function createFromArray(Adherent $referent, array $data): self
    {
        $message = new self($referent, ManagedUsersFilter::createFromArray($data['filter']));
        $message->subject = $data['subject'];
        $message->content = $data['content'];

        return $message;
    }

    public function toArray(): array
    {
        return [
            'referent_uuid' => $this->from->getUuid()->toString(),
            'filter' => $this->filter->toArray(),
            'subject' => $this->subject,
            'content' => $this->content,
        ];
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

    public function getFilter(): ManagedUsersFilter
    {
        return $this->filter;
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

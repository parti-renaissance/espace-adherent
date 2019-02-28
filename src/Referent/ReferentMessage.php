<?php

namespace AppBundle\Referent;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentManagedUsersMessage;
use AppBundle\Validator\WysiwygLength as AssertWysiwygLength;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ReferentMessage
{
    /**
     * @var UuidInterface
     */
    private $uuid;

    /**
     * @var Adherent
     */
    private $from;

    /**
     * @var ManagedUsersFilter
     *
     * @Assert\Valid
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
     * @AssertWysiwygLength(
     *     min=10,
     *     max=6000,
     *     minMessage="referent.message.min_length",
     *     maxMessage="referent.message.max_length",
     * )
     */
    private $content;

    public function __construct(UuidInterface $uuid, Adherent $from, ManagedUsersFilter $filter)
    {
        $this->uuid = $uuid;
        $this->from = $from;
        $this->filter = $filter;
    }

    public static function create(Adherent $referent, ManagedUsersFilter $filter): self
    {
        return new self(Uuid::uuid4(), $referent, $filter);
    }

    public static function createFromMessage(ReferentManagedUsersMessage $savedMessage): self
    {
        $message = new self($savedMessage->getUuid(), $savedMessage->getFrom(), ManagedUsersFilter::createFromMessage($savedMessage));
        $message->setSubject($savedMessage->getSubject());
        $message->setContent($savedMessage->getContent());

        return $message;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
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

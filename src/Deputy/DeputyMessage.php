<?php

namespace AppBundle\Deputy;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\DeputyManagedUsersMessage;
use AppBundle\Entity\District;
use AppBundle\Validator\WysiwygLength as AssertWysiwygLength;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Validator\Constraints as Assert;

class DeputyMessage
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
     * @var string|null
     *
     * @Assert\NotBlank
     */
    private $subject;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="message.not_blank")
     * @AssertWysiwygLength(
     *     min=10,
     *     max=6000,
     *     minMessage="message.min_length",
     *     maxMessage="message.max_length",
     * )
     */
    private $content;

    /**
     * @var District
     *
     * @Assert\Valid
     */
    private $district;

    /**
     * @var int
     *
     * @Assert\Valid
     */
    private $offset;

    public function __construct(UuidInterface $uuid, Adherent $deputy)
    {
        $this->uuid = $uuid;
        $this->from = $deputy;
        $this->district = $deputy->getManagedDistrict();
    }

    public static function create(Adherent $deputy): self
    {
        return new self(Uuid::uuid4(), $deputy);
    }

    public static function createFromMessage(DeputyManagedUsersMessage $savedMessage): self
    {
        $message = new self($savedMessage->getUuid(), $savedMessage->getFrom());
        $message->setSubject($savedMessage->getSubject());
        $message->setContent($savedMessage->getContent());
        $message->setOffset($savedMessage->getOffset());

        return $message;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
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

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getDistrict(): District
    {
        return $this->district;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function setOffset(int $offset = null): void
    {
        $this->offset = $offset;
    }
}

<?php

namespace AppBundle\Entity\AdherentMessage;

use ApiPlatform\Core\Annotation\ApiResource;
use AppBundle\AdherentMessage\AdherentMessageDataObject;
use AppBundle\AdherentMessage\AdherentMessageStatusEnum;
use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\EntityIdentityTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="adherent_messages")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     AdherentMessageTypeEnum::REFERENT: "ReferentAdherentMessage",
 *     AdherentMessageTypeEnum::DEPUTY: "DeputyAdherentMessage",
 *     AdherentMessageTypeEnum::COMMITTEE: "CommitteeAdherentMessage",
 *     AdherentMessageTypeEnum::CITIZEN_PROJECT: "CitizenProjectAdherentMessage"
 * })
 *
 * @ApiResource(
 *     shortName="AdherentMessage",
 *     collectionOperations={},
 *     itemOperations={
 *         "get": {
 *             "access_control": "object.getAuthor() == user",
 *             "requirements": {"id": "%pattern_uuid%"},
 *             "normalization_context": {"groups": {"message_read"}}
 *         }
 *     }
 * )
 */
abstract class AbstractAdherentMessage implements AdherentMessageInterface
{
    use EntityIdentityTrait;
    use TimestampableEntity;

    /**
     * @var Adherent
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Adherent")
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Groups({"message_read"})
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(nullable=true)
     */
    private $externalId;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Groups({"message_read"})
     */
    private $status = AdherentMessageStatusEnum::DRAFT;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default": false})
     *
     * @Groups({"message_read"})
     */
    private $synchronized = false;

    /**
     * @var AdherentMessageFilterInterface|null
     *
     * @ORM\OneToOne(
     *     targetEntity="AppBundle\Entity\AdherentMessage\Filter\AbstractAdherentMessageFilter",
     *     inversedBy="message",
     *     cascade={"all"},
     *     fetch="EAGER",
     *     orphanRemoval=true
     * )
     */
    private $filter;

    /**
     * @var int|null
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({"message_read"})
     */
    private $recipientCount;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sentAt;

    public function __construct(UuidInterface $uuid, Adherent $author)
    {
        $this->uuid = $uuid;
        $this->author = $author;
    }

    public static function createFromAdherent(Adherent $adherent): self
    {
        return new static(Uuid::uuid4(), $adherent);
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function setExternalId(string $externalId): void
    {
        $this->externalId = $externalId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function markAsSent(): void
    {
        $this->sentAt = new \DateTime();
        $this->status = AdherentMessageStatusEnum::SENT_SUCCESSFULLY;
    }

    public function isSent(): bool
    {
        return AdherentMessageStatusEnum::SENT_SUCCESSFULLY === $this->status;
    }

    public function setSynchronized(bool $value): void
    {
        $this->synchronized = $value;
    }

    public function isSynchronized(): bool
    {
        return $this->synchronized && (null === $this->filter || $this->filter->isSynchronized());
    }

    public function getFilter(): ?AdherentMessageFilterInterface
    {
        return $this->filter;
    }

    public function setFilter(AdherentMessageFilterInterface $filter): void
    {
        $this->filter = $filter;
    }

    public function resetFilter(): void
    {
        $this->recipientCount = $this->filter = null;
    }

    public function getRecipientCount(): ?int
    {
        return $this->recipientCount;
    }

    public function setRecipientCount(?int $recipientCount): void
    {
        $this->recipientCount = $recipientCount;
    }

    public function getFromName(): ?string
    {
        return $this->author ? $this->author->getFullName() : null;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function updateFromDataObject(AdherentMessageDataObject $dataObject): AdherentMessageInterface
    {
        if ($dataObject->getContent()) {
            $this->setContent($dataObject->getContent());
        }

        if ($dataObject->getLabel()) {
            $this->setLabel($dataObject->getLabel());
        }

        if ($dataObject->getSubject()) {
            $this->setSubject($dataObject->getSubject());
        }

        return $this;
    }

    public function hasReadOnlyFilter(): bool
    {
        return false;
    }
}

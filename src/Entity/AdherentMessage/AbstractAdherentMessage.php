<?php

namespace AppBundle\Entity\AdherentMessage;

use ApiPlatform\Core\Annotation\ApiResource;
use AppBundle\AdherentMessage\AdherentMessageDataObject;
use AppBundle\AdherentMessage\AdherentMessageStatusEnum;
use AppBundle\AdherentMessage\AdherentMessageTypeEnum;
use AppBundle\AdherentMessage\Filter\AdherentMessageFilterInterface;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\EntityIdentityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AdherentMessageRepository")
 * @ORM\Table(name="adherent_messages")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @ORM\DiscriminatorMap({
 *     AdherentMessageTypeEnum::REFERENT: "ReferentAdherentMessage",
 *     AdherentMessageTypeEnum::DEPUTY: "DeputyAdherentMessage",
 *     AdherentMessageTypeEnum::COMMITTEE: "CommitteeAdherentMessage",
 *     AdherentMessageTypeEnum::CITIZEN_PROJECT: "CitizenProjectAdherentMessage",
 *     AdherentMessageTypeEnum::MUNICIPAL_CHIEF: "MunicipalChiefAdherentMessage"
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
     * @ORM\Column
     *
     * @Groups({"message_read"})
     */
    private $status = AdherentMessageStatusEnum::DRAFT;

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
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sentAt;

    /**
     * @var MailchimpCampaign[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity="AppBundle\Entity\AdherentMessage\MailchimpCampaign",
     *     mappedBy="message",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    private $mailchimpCampaigns;

    public function __construct(UuidInterface $uuid, Adherent $author)
    {
        $this->uuid = $uuid;
        $this->author = $author;
        $this->mailchimpCampaigns = new ArrayCollection();
    }

    public static function createFromAdherent(Adherent $adherent): self
    {
        return new static(Uuid::uuid4(), $adherent);
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @Groups({"message_read"})
     */
    public function isSynchronized(): bool
    {
        if ($this->mailchimpCampaigns->isEmpty()) {
            return false;
        }

        $status = true;

        foreach ($this->mailchimpCampaigns as $campaign) {
            $status &= $campaign->isSynchronized();
        }

        return $status;
    }

    public function setSynchronized(bool $value): void
    {
        $this->mailchimpCampaigns->forAll(static function (int $key, MailchimpCampaign $campaign) use ($value) {
            $campaign->setSynchronized($value);
        });
    }

    public function getFilter(): ?AdherentMessageFilterInterface
    {
        return $this->filter;
    }

    public function setFilter(?AdherentMessageFilterInterface $filter): void
    {
        $this->resetFilter();

        if ($filter) {
            $filter->setMessage($this);
        }

        $this->filter = $filter;
    }

    public function resetFilter(): void
    {
        $this->mailchimpCampaigns->forAll(static function (int $key, MailchimpCampaign $campaign) {
            $campaign->reset();
        });
    }

    /**
     * @Groups({"message_read"})
     */
    public function getRecipientCount(): ?int
    {
        return array_sum($this->mailchimpCampaigns
            ->map(static function (MailchimpCampaign $campaign) {
                return $campaign->getRecipientCount();
            })
            ->toArray()
        );
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

    /** @return MailchimpCampaign[] */
    public function getMailchimpCampaigns(): array
    {
        return $this->mailchimpCampaigns->toArray();
    }

    public function addMailchimpCampaign(MailchimpCampaign $campaign): void
    {
        if (!$this->mailchimpCampaigns->contains($campaign)) {
            $this->mailchimpCampaigns->add($campaign);
        }
    }

    public function setMailchimpCampaigns(array $campaigns): void
    {
        $this->mailchimpCampaigns->clear();

        foreach ($campaigns as $campaign) {
            $this->addMailchimpCampaign($campaign);
        }
    }
}

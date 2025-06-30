<?php

namespace App\Entity\AdherentMessage;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\AdherentMessage\AdherentMessageDataObject;
use App\AdherentMessage\AdherentMessageStatusEnum;
use App\AdherentMessage\AdherentMessageTypeEnum;
use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\Api\Filter\AdherentMessageScopeFilter;
use App\Controller\Api\AdherentMessage\DuplicateMessageController;
use App\Controller\Api\AdherentMessage\GetAdherentMessageKpiController;
use App\Controller\Api\AdherentMessage\GetAdherentMessageRecipientsCountController;
use App\Controller\Api\AdherentMessage\SendAdherentMessageController;
use App\Controller\Api\AdherentMessage\SendTestAdherentMessageController;
use App\Controller\Api\AdherentMessage\UpdateAdherentMessageFilterController;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\Filter\AbstractAdherentMessageFilter;
use App\Entity\AuthoredTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\UnlayerJsonContentTrait;
use App\Repository\AdherentMessageRepository;
use App\Validator\ValidAuthorRoleMessageType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @phpstan-consistent-constructor
 */
#[ApiFilter(filterClass: AdherentMessageScopeFilter::class)]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['createdAt'])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['label' => 'partial', 'status' => 'exact'])]
#[ApiResource(
    shortName: 'AdherentMessage',
    operations: [
        new Get(
            uriTemplate: '/v3/adherent_messages/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['message_read']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', ['messages', 'messages_vox']) and (object.getAuthor() == user or user.hasDelegatedFromUser(object.getAuthor(), 'messages') or user.hasDelegatedFromUser(object.getAuthor(), 'messages_vox'))"
        ),
        new Get(
            uriTemplate: '/v3/adherent_messages/{uuid}/content',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['message_read_content']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', ['messages', 'messages_vox']) and (object.getAuthor() == user or user.hasDelegatedFromUser(object.getAuthor(), 'messages') or user.hasDelegatedFromUser(object.getAuthor(), 'messages_vox'))"
        ),
        new Get(
            uriTemplate: '/v3/adherent_messages/{uuid}/count-recipients',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: GetAdherentMessageRecipientsCountController::class,
            security: "is_granted('REQUEST_SCOPE_GRANTED', ['messages', 'messages_vox']) and (object.getAuthor() == user or user.hasDelegatedFromUser(object.getAuthor(), 'messages') or user.hasDelegatedFromUser(object.getAuthor(), 'messages_vox'))",
        ),
        new Put(
            uriTemplate: '/v3/adherent_messages/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['message_read']],
            security: "is_granted('REQUEST_SCOPE_GRANTED', ['messages', 'messages_vox']) and (object.getAuthor() == user or user.hasDelegatedFromUser(object.getAuthor(), 'messages') or user.hasDelegatedFromUser(object.getAuthor(), 'messages_vox'))"
        ),
        new HttpOperation(
            method: 'POST',
            uriTemplate: '/v3/adherent_messages/{uuid}/send',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: SendAdherentMessageController::class,
            deserialize: false
        ),
        new HttpOperation(
            method: 'POST',
            uriTemplate: '/v3/adherent_messages/{uuid}/send-test',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: SendTestAdherentMessageController::class,
            deserialize: false
        ),
        new HttpOperation(
            method: 'PUT',
            uriTemplate: '/v3/adherent_messages/{uuid}/filter',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: UpdateAdherentMessageFilterController::class,
            deserialize: false
        ),
        new HttpOperation(
            method: 'POST',
            uriTemplate: '/v3/adherent_messages/{uuid}/duplicate',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: DuplicateMessageController::class,
            deserialize: false
        ),
        new Delete(
            uriTemplate: '/v3/adherent_messages/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "is_granted('REQUEST_SCOPE_GRANTED', ['messages', 'messages_vox']) and not object.isSent() and (object.getAuthor() == user or user.hasDelegatedFromUser(object.getAuthor(), 'messages') or user.hasDelegatedFromUser(object.getAuthor(), 'messages_vox'))"
        ),
        new GetCollection(
            uriTemplate: '/v3/adherent_messages',
            normalizationContext: ['groups' => ['message_read_list']]
        ),
        new Post(
            uriTemplate: '/v3/adherent_messages',
            normalizationContext: ['groups' => ['message_read']]
        ),
        new GetCollection(
            uriTemplate: '/v3/adherent_messages/kpi',
            controller: GetAdherentMessageKpiController::class
        ),
    ],
    normalizationContext: ['groups' => ['message_read_list']],
    denormalizationContext: ['groups' => ['message_write']],
    paginationClientEnabled: true,
    security: "is_granted('REQUEST_SCOPE_GRANTED', ['messages', 'messages_vox'])"
)]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap(AdherentMessageTypeEnum::CLASSES)]
#[ORM\Entity(repositoryClass: AdherentMessageRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\Table(name: 'adherent_messages')]
#[ValidAuthorRoleMessageType]
abstract class AbstractAdherentMessage implements AdherentMessageInterface
{
    use EntityIdentityTrait;
    use UnlayerJsonContentTrait;
    use EntityTimestampableTrait;
    use AuthoredTrait;

    /**
     * @var Adherent
     */
    #[Assert\NotBlank]
    #[Groups(['message_read_list', 'message_read'])]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    protected $author;

    /**
     * @var string
     */
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['message_read', 'message_read_list', 'message_write'])]
    #[ORM\Column]
    private $label;

    /**
     * @var string
     */
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    #[Groups(['message_read', 'message_read_list', 'message_write', 'message_read_content'])]
    #[ORM\Column]
    private $subject;

    /**
     * @var string
     */
    #[Groups(['message_read_status', 'message_read', 'message_read_list'])]
    #[ORM\Column]
    private $status = AdherentMessageStatusEnum::DRAFT;

    /**
     * @var AdherentMessageFilterInterface|null
     */
    #[ORM\OneToOne(inversedBy: 'message', targetEntity: AbstractAdherentMessageFilter::class, cascade: ['all'], fetch: 'EAGER', orphanRemoval: true)]
    private $filter;

    /**
     * @var \DateTimeInterface|null
     */
    #[Groups(['message_read_list'])]
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $sentAt;

    /**
     * @var MailchimpCampaign[]|Collection
     */
    #[ORM\OneToMany(mappedBy: 'message', targetEntity: MailchimpCampaign::class, cascade: ['all'], orphanRemoval: true)]
    private $mailchimpCampaigns;

    /**
     * @var int|null
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    private $recipientCount;

    /**
     * @var string
     */
    #[Groups(['message_read', 'message_read_list'])]
    #[ORM\Column(options: ['default' => self::SOURCE_PLATFORM])]
    private $source = self::SOURCE_PLATFORM;

    public function __construct(?UuidInterface $uuid = null, ?Adherent $author = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->author = $author;
        $this->mailchimpCampaigns = new ArrayCollection();
    }

    public static function createFromAdherent(Adherent $adherent, ?UuidInterface $uuid = null): AdherentMessageInterface
    {
        return new static($uuid ?? Uuid::uuid4(), $adherent);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
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

    #[Groups(['message_read_status', 'message_read', 'message_read_list'])]
    public function isSynchronized(): bool
    {
        if ($this->mailchimpCampaigns->isEmpty()) {
            return false;
        }

        $status = true;

        foreach ($this->getMailchimpCampaigns() as $campaign) {
            $status &= $campaign->isSynchronized();
        }

        $status &= (!$this->filter || $this->filter->isSynchronized());

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
        if ($this->filter !== $filter) {
            $this->resetFilter();
        }

        $filter?->setMessage($this);

        $this->filter = $filter;
    }

    public function resetFilter(): void
    {
        array_map(static function (MailchimpCampaign $campaign) {
            $campaign->reset();
        }, $this->getMailchimpCampaigns());
    }

    #[Groups(['message_read_status', 'message_read', 'message_read_list'])]
    public function getRecipientCount(): ?int
    {
        return $this->recipientCount + array_sum(
            array_map(static function (MailchimpCampaign $campaign) {
                return $campaign->getRecipientCount();
            }, $this->getMailchimpCampaigns())
        );
    }

    public function setRecipientCount(?int $recipientCount): void
    {
        $this->recipientCount = $recipientCount;
    }

    #[Groups('message_read_list')]
    public function getFromName(): ?string
    {
        return ($this->author ? trim($this->author->getFullName()) : null).$this->getFromNameSuffix();
    }

    protected function getFromNameSuffix(): string
    {
        return ' | Renaissance';
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

    public function isMailchimp(): bool
    {
        return $this instanceof CampaignAdherentMessageInterface;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    public function isCompatibleWithScope(string $scope): bool
    {
        return $scope === $this->getScope();
    }

    public function getMailchimpId(): ?string
    {
        foreach ($this->mailchimpCampaigns as $campaign) {
            if ($campaign->isSynchronized() && $campaign->getExternalId()) {
                return $campaign->getExternalId();
            }
        }

        return null;
    }

    protected function getScope(): ?string
    {
        return null;
    }

    public function __clone(): void
    {
        $this->id = null;
        $this->uuid = Uuid::uuid4();
        $this->mailchimpCampaigns = new ArrayCollection();
        $this->status = AdherentMessageStatusEnum::DRAFT;
        $this->recipientCount = 0;
        $this->sentAt = null;
        $this->filter = null;
        $this->label = $this->label.' - Copie';
        $this->createdAt = $this->updatedAt = new \DateTime();
    }
}

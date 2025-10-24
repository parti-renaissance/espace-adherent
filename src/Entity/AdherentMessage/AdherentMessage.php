<?php

namespace App\Entity\AdherentMessage;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
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
use App\AdherentMessage\AdherentMessageStatusEnum;
use App\AdherentMessage\Filter\AdherentMessageFilterInterface;
use App\AdherentMessage\MailchimpStatusEnum;
use App\Api\Filter\AdherentMessageScopeFilter;
use App\Controller\Api\AdherentMessage\DuplicateMessageController;
use App\Controller\Api\AdherentMessage\GetAdherentMessageKpiController;
use App\Controller\Api\AdherentMessage\GetAdherentMessageRecipientsCountController;
use App\Controller\Api\AdherentMessage\GetAvailableSendersController;
use App\Controller\Api\AdherentMessage\SendAdherentMessageController;
use App\Controller\Api\AdherentMessage\SendTestAdherentMessageController;
use App\Controller\Api\AdherentMessage\UpdateAdherentMessageFilterController;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\Filter\AbstractAdherentMessageFilter;
use App\Entity\AuthorInstanceTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\NotificationObjectInterface;
use App\Entity\UnlayerJsonContentTrait;
use App\EntityListener\AlgoliaIndexListener;
use App\JeMengage\Hit\HitTargetInterface;
use App\JeMengage\Push\Command\SendNotificationCommandInterface;
use App\Normalizer\ImageExposeNormalizer;
use App\Repository\AdherentMessageRepository;
use App\Scope\Scope;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiFilter(filterClass: AdherentMessageScopeFilter::class)]
#[ApiFilter(filterClass: OrderFilter::class, properties: ['createdAt'])]
#[ApiFilter(filterClass: SearchFilter::class, properties: ['label' => 'partial', 'status' => 'exact'])]
#[ApiFilter(filterClass: BooleanFilter::class, properties: ['isStatutory'])]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/adherent_messages/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['message_read', ImageExposeNormalizer::NORMALIZATION_GROUP]],
            security: "is_granted('ROLE_USER')",
        ),
        new Get(
            uriTemplate: '/adherent_messages/{uuid}/content',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['message_read_content']],
            security: "is_granted('CAN_EDIT_PUBLICATION', object)"
        ),
        new Get(
            uriTemplate: '/adherent_messages/{uuid}/count-recipients',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: GetAdherentMessageRecipientsCountController::class,
            security: "is_granted('CAN_EDIT_PUBLICATION', object)",
        ),
        new Get(
            uriTemplate: '/adherent_messages/available-senders',
            controller: GetAvailableSendersController::class,
            read: false,
        ),
        new Put(
            uriTemplate: '/adherent_messages/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            normalizationContext: ['groups' => ['message_read', ImageExposeNormalizer::NORMALIZATION_GROUP]],
            security: "not object.isSent() and is_granted('CAN_EDIT_PUBLICATION', object)",
        ),
        new HttpOperation(
            method: 'POST',
            uriTemplate: '/adherent_messages/{uuid}/send',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: SendAdherentMessageController::class,
            security: "is_granted('CAN_EDIT_PUBLICATION', object)",
            deserialize: false,
        ),
        new HttpOperation(
            method: 'POST',
            uriTemplate: '/adherent_messages/{uuid}/send-test',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: SendTestAdherentMessageController::class,
            security: "is_granted('CAN_EDIT_PUBLICATION', object)",
            deserialize: false,
        ),
        new HttpOperation(
            method: 'PUT',
            uriTemplate: '/adherent_messages/{uuid}/filter',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: UpdateAdherentMessageFilterController::class,
            security: "is_granted('CAN_EDIT_PUBLICATION', object)",
            deserialize: false
        ),
        new HttpOperation(
            method: 'POST',
            uriTemplate: '/adherent_messages/{uuid}/duplicate',
            requirements: ['uuid' => '%pattern_uuid%'],
            controller: DuplicateMessageController::class,
            security: "is_granted('CAN_EDIT_PUBLICATION', object)",
            deserialize: false,
        ),
        new Delete(
            uriTemplate: '/adherent_messages/{uuid}',
            requirements: ['uuid' => '%pattern_uuid%'],
            security: "not object.isSent() and is_granted('CAN_EDIT_PUBLICATION', object)"
        ),
        new GetCollection(
            uriTemplate: '/adherent_messages',
            normalizationContext: ['groups' => ['message_read_list', ImageExposeNormalizer::NORMALIZATION_GROUP]]
        ),
        new Post(
            uriTemplate: '/adherent_messages',
            normalizationContext: ['groups' => ['message_read', ImageExposeNormalizer::NORMALIZATION_GROUP]]
        ),
        new GetCollection(
            uriTemplate: '/adherent_messages/kpi',
            controller: GetAdherentMessageKpiController::class,
        ),
    ],
    routePrefix: '/v3',
    normalizationContext: ['groups' => ['message_read_list', ImageExposeNormalizer::NORMALIZATION_GROUP]],
    denormalizationContext: ['groups' => ['message_write']],
    paginationClientEnabled: true,
    security: "is_granted('REQUEST_SCOPE_GRANTED', ['messages', 'publications'])"
)]
#[ORM\Entity(repositoryClass: AdherentMessageRepository::class)]
#[ORM\EntityListeners([AlgoliaIndexListener::class])]
#[ORM\Index(fields: ['status'])]
#[ORM\Index(fields: ['source'])]
#[ORM\Index(fields: ['instanceScope'])]
#[ORM\Table(name: 'adherent_messages')]
class AdherentMessage implements AdherentMessageInterface, NotificationObjectInterface, HitTargetInterface
{
    use EntityIdentityTrait;
    use UnlayerJsonContentTrait;
    use EntityTimestampableTrait;
    use AuthorInstanceTrait;

    /**
     * @var Adherent
     */
    #[Assert\NotBlank]
    #[Groups(['message_read_list', 'message_read'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    protected $author;

    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $teamOwner = null;

    #[Groups(['message_read_list', 'message_read', 'message_write'])]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    #[ORM\ManyToOne]
    private ?Adherent $sender = null;

    #[ORM\Column(nullable: true)]
    public ?string $senderEmail = null;

    #[ORM\Column(nullable: true)]
    public ?string $senderRole = null;

    #[ORM\Column(nullable: true)]
    public ?string $senderName = null;

    #[ORM\Column(nullable: true)]
    public ?string $senderInstance = null;

    #[ORM\Column(nullable: true)]
    public ?string $senderZone = null;

    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $senderTheme = null;

    /**
     * @var string
     */
    #[Assert\Length(max: 255)]
    #[Groups(['message_read', 'message_read_list', 'message_write'])]
    #[ORM\Column(nullable: true)]
    private $label;

    /**
     * @var string
     */
    #[Assert\Length(max: 255)]
    #[Groups(['message_read', 'message_read_list', 'message_write', 'message_read_content'])]
    #[ORM\Column(nullable: true)]
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
    #[Groups(['message_read_list', 'message_read'])]
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
    #[ORM\Column(options: ['default' => self::SOURCE_CADRE])]
    private $source = self::SOURCE_CADRE;

    #[ORM\Column(nullable: true)]
    private ?string $instanceScope = null;

    #[Groups(['message_read', 'message_read_list', 'message_write'])]
    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isStatutory = false;

    public function __construct(?UuidInterface $uuid = null, ?Adherent $author = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->author = $this->sender = $author;
        $this->mailchimpCampaigns = new ArrayCollection();
    }

    public static function createFromAdherent(Adherent $adherent, ?UuidInterface $uuid = null): AdherentMessageInterface
    {
        return new self($uuid ?? Uuid::uuid4(), $adherent);
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

    public function setLabel(?string $label): void
    {
        $this->label = $label;
    }

    public function setSubject(?string $subject): void
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
        if ($this->isStatutory) {
            return true;
        }

        if ($this->mailchimpCampaigns->isEmpty()) {
            return false;
        }

        $status = true;

        foreach ($this->getMailchimpCampaigns() as $campaign) {
            $status &= $campaign->isSynchronized();
        }

        $status &= (!$this->filter || $this->filter->isSynchronized());

        return (bool) $status;
    }

    public function isFullySent(): bool
    {
        $status = true;

        foreach ($this->getMailchimpCampaigns() as $campaign) {
            $status &= (MailchimpStatusEnum::Sent === $campaign->status);
        }

        return (bool) $status;
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
        if ($this->senderName) {
            return trim($this->senderName).$this->getFromNameSuffix();
        }

        if ($this->sender) {
            return trim($this->sender->getFullName()).$this->getFromNameSuffix();
        }

        if ($this->author) {
            return trim($this->author->getFullName()).$this->getFromNameSuffix();
        }

        return null;
    }

    private function getFromNameSuffix(): string
    {
        return ' | Renaissance';
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
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

    public function getSource(): string
    {
        return $this->source;
    }

    public function setSource(string $source): void
    {
        $this->source = $source;
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

    public function getSender(): ?Adherent
    {
        return $this->sender;
    }

    public function updateSenderDataFromScope(Scope $scope): void
    {
        $this->senderInstance = $scope->getScopeInstance();
        $this->senderRole = $scope->getMainRoleName();
        $this->senderTheme = $scope->getAttribute('theme');
        $this->senderZone = implode(', ', $scope->getZoneNames());
    }

    public function setSender(Adherent $sender): void
    {
        if ($this->isSent()) {
            throw new \LogicException('Cannot change sender of a sent message.');
        }

        $this->sender = $sender;
        $this->senderEmail = $sender->getEmailAddress();
        $this->senderName = $sender->getFullName();
    }

    public function getInstanceScope(): ?string
    {
        return $this->instanceScope;
    }

    public function setInstanceScope(?string $instanceScope): void
    {
        $this->instanceScope = $instanceScope;
    }

    public function isStatutory(): bool
    {
        return $this->isStatutory;
    }

    public function setIsStatutory(bool $isStatutory): void
    {
        $this->isStatutory = $isStatutory;
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
        $this->label .= ' - Copie';
        $this->createdAt = $this->updatedAt = new \DateTime();
    }

    public function isIndexable(): bool
    {
        return $this->isSent() && self::SOURCE_VOX === $this->source;
    }

    public function isNotificationEnabled(SendNotificationCommandInterface $command): bool
    {
        return true;
    }

    public function handleNotificationSent(SendNotificationCommandInterface $command): void
    {
    }

    public function getUnsubscribedCount(): int
    {
        return (int) array_sum(array_filter(array_map(
            static fn (MailchimpCampaign $campaign) => $campaign->getReport()?->getUnsubscribed(),
            $this->getMailchimpCampaigns()
        )));
    }
}

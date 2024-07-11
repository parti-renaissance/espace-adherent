<?php

namespace App\Entity;

use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Repository\CommitteeFeedItemRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[ORM\Entity(repositoryClass: CommitteeFeedItemRepository::class)]
class CommitteeFeedItem implements UserDocumentInterface
{
    use EntityIdentityTrait;
    use UserDocumentTrait;

    public const MESSAGE = 'message';
    public const EVENT = 'event';

    #[ORM\Column(length: 18)]
    private $itemType;

    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Committee::class)]
    private $committee;

    /**
     * @var Adherent Any host of the committee
     */
    #[ORM\ManyToOne(targetEntity: Adherent::class, inversedBy: 'committeeFeedItems')]
    private $author;

    /**
     * @var CommitteeEvent
     */
    #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: BaseEvent::class, fetch: 'EAGER')]
    private $event;

    #[ORM\Column(type: 'text', nullable: true)]
    private $content;

    #[ORM\Column(type: 'boolean', options: ['default' => true])]
    private $published = true;

    /**
     * @var \DateTime|null
     */
    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    /**
     * @var UserDocument[]|Collection
     */
    #[ORM\InverseJoinColumn(name: 'user_document_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\JoinColumn(name: 'committee_feed_item_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\JoinTable(name: 'committee_feed_item_user_documents')]
    #[ORM\ManyToMany(targetEntity: UserDocument::class, cascade: ['all'], orphanRemoval: true)]
    protected Collection $documents;

    private function __construct(
        UuidInterface $uuid,
        string $type,
        Committee $committee,
        Adherent $author,
        bool $published = true,
        string $createdAt = 'now'
    ) {
        $this->uuid = $uuid;
        $this->committee = $committee;
        $this->author = $author;
        $this->itemType = $type;
        $this->published = $published;
        $this->createdAt = new \DateTime($createdAt);
        $this->documents = new ArrayCollection();
    }

    public static function createMessage(
        Committee $committee,
        Adherent $author,
        string $content,
        bool $published = true,
        string $createdAt = 'now'
    ): self {
        $item = new self(Uuid::uuid4(), self::MESSAGE, $committee, $author, $published, $createdAt);
        $item->content = $content;

        return $item;
    }

    public static function createEvent(
        CommitteeEvent $event,
        Adherent $author,
        bool $published = true,
        string $createdAt = 'now'
    ): self {
        $item = new self(
            Uuid::uuid5(Uuid::NAMESPACE_OID, (string) $event->getUuid()),
            self::EVENT,
            $event->getCommittee(),
            $author,
            $published,
            $createdAt
        );
        $item->event = $event;

        return $item;
    }

    public function getContent(): ?string
    {
        if (!$this->content && $this->event instanceof CommitteeEvent) {
            return $this->event->getDescription();
        }

        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function isPublished(): bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): void
    {
        $this->published = $published;
    }

    public function getCommittee(): ?Committee
    {
        return $this->committee;
    }

    public function getEvent(): ?BaseEvent
    {
        return $this->event;
    }

    public function getAuthor(): ?Adherent
    {
        return $this->author;
    }

    public function getType(): string
    {
        return $this->itemType;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public static function getItemTypes(bool $includeMessages): array
    {
        $types[] = self::EVENT;

        if ($includeMessages) {
            $types[] = self::MESSAGE;
        }

        return $types;
    }

    public function getAuthorFirstName(): ?string
    {
        if ($this->author instanceof Adherent) {
            return $this->author->getFirstName();
        }

        return null;
    }

    public function getContentContainingDocuments(): string
    {
        return (string) $this->content;
    }

    public function getFieldContainingDocuments(): string
    {
        return 'content';
    }
}

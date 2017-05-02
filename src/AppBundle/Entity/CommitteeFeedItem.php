<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommitteeFeedItemRepository")
 *
 * @Algolia\Index(autoIndex=false)
 */
class CommitteeFeedItem
{
    const MESSAGE = 'message';
    const EVENT = 'event';

    use EntityIdentityTrait;

    /**
     * @ORM\Column(length=15)
     */
    private $itemType;

    /**
     * @ORM\ManyToOne(targetEntity="Committee")
     */
    private $committee;

    /**
     * @var Adherent Any host of the committee
     *
     * @ORM\ManyToOne(targetEntity="Adherent")
     */
    private $author;

    /**
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Event", fetch="EAGER")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     */
    private $event;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @var \DateTime|\DateTime|null
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    private function __construct(
        UuidInterface $uuid,
        string $type,
        Committee $committee,
        Adherent $author,
        string $createdAt = 'now'
    ) {
        $this->uuid = $uuid;
        $this->committee = $committee;
        $this->author = $author;
        $this->itemType = $type;
        $this->createdAt = new \DateTime($createdAt);
    }

    public static function createMessage(
        Committee $committee,
        Adherent $author,
        string $content,
        string $createdAt = 'now'
    ): self {
        $item = new static(Uuid::uuid4(), self::MESSAGE, $committee, $author, $createdAt);
        $item->content = $content;

        return $item;
    }

    public static function createEvent(
        Event $event,
        Adherent $author,
        string $createdAt = 'now'
    ): self {
        $item = new static(
            Uuid::uuid5(Uuid::NAMESPACE_OID, (string) $event->getUuid()),
            self::EVENT,
            $event->getCommittee(),
            $author,
            $createdAt
        );
        $item->event = $event;

        return $item;
    }

    public function getContent(): ?string
    {
        if ($this->event instanceof Event) {
            return $this->event->getDescription();
        }

        return $this->content;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function getAuthor(): Adherent
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
    }
}

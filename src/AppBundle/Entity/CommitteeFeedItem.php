<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CommitteeFeedItemRepository")
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
     * @var CommitteeEvent
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CommitteeEvent", fetch="EAGER")
     * @ORM\JoinColumn(onDelete="SET NULL", nullable=true)
     */
    private $event;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @var \DateTimeImmutable|\DateTime|null
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
        $this->createdAt = new \DateTimeImmutable($createdAt);
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
        CommitteeEvent $event,
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
        return $this->content;
    }

    public function getEvent(): ?CommitteeEvent
    {
        return $this->event;
    }
}

<?php

namespace App\Entity;

use App\Firebase\Notification\MulticastNotificationInterface;
use App\Firebase\Notification\NotificationInterface;
use App\Firebase\Notification\TopicNotificationInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NotificationRepository")
 */
class Notification
{
    use EntityTimestampableTrait;

    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $notificationClass;

    /**
     * @var string
     *
     * @ORM\Column
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $body;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $deliveredAt;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $topic;

    /**
     * @var string|null
     *
     * @ORM\Column(type="simple_array", nullable=true))
     */
    private $tokens;

    public function __construct(
        string $notificationClass,
        string $title,
        string $body,
        string $topic = null,
        array $tokens = null
    ) {
        $this->notificationClass = $notificationClass;
        $this->title = $title;
        $this->body = $body;
        $this->topic = $topic;
        $this->tokens = $tokens;
    }

    public static function create(NotificationInterface $notification): self
    {
        $parts = explode('\\', \get_class($notification));

        return new self(
            end($parts),
            $notification->getTitle(),
            $notification->getBody(),
            $notification instanceof TopicNotificationInterface
                ? $notification->getTopic()
                : null,
            $notification instanceof MulticastNotificationInterface
                ? $notification->getTokens()
                : null
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNotificationClass(): string
    {
        return $this->notificationClass;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function getTokens(): ?array
    {
        return $this->tokens;
    }

    public function getDelivered(): ?\DateTimeInterface
    {
        return $this->deliveredAt;
    }

    public function setDelivered(): void
    {
        $this->deliveredAt = new \DateTime();
    }
}

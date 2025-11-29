<?php

declare(strict_types=1);

namespace App\Entity;

use App\Firebase\Notification\NotificationInterface;
use App\Firebase\Notification\TopicNotificationInterface;
use App\Repository\NotificationRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    /**
     * @var string
     */
    #[ORM\Column]
    private $notificationClass;

    /**
     * @var string
     */
    #[ORM\Column]
    private $title;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text')]
    private $body;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime', nullable: true)]
    private $deliveredAt;

    /**
     * @var string|null
     */
    #[ORM\Column(nullable: true)]
    private $topic;

    #[ORM\Column(type: 'simple_array', nullable: true)]
    private ?array $tokens;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $data;

    #[ORM\Column(nullable: true)]
    private ?string $scope;

    #[ORM\Column(unique: true, nullable: true)]
    public ?string $notificationKey = null;

    public function __construct(
        string $notificationClass,
        string $title,
        string $body,
        ?array $data = null,
        ?string $scope = null,
        ?string $topic = null,
        ?array $tokens = null,
    ) {
        $this->uuid = Uuid::uuid4();
        $this->notificationClass = $notificationClass;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
        $this->scope = $scope;
        $this->topic = $topic;
        $this->tokens = $tokens;

        $this->notificationKey = $this->generateNotificationKey();
    }

    public static function create(NotificationInterface $notification): self
    {
        $parts = explode('\\', $notification::class);

        return new self(
            end($parts),
            $notification->getTitle(),
            $notification->getBody(),
            $notification->getData(),
            $notification->getScope(),
            $notification instanceof TopicNotificationInterface ? $notification->getTopic() : null,
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

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function getTokensCount(): int
    {
        return \count($this->tokens);
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

    public function withTokens(array $tokens): self
    {
        $copy = clone $this;
        $copy->tokens = $tokens;

        $copy->notificationKey = $copy->generateNotificationKey();

        return $copy;
    }

    private function generateNotificationKey(): string
    {
        $tokens = $this->tokens;

        $tokens && sort($tokens);

        $data = [
            'class' => $this->notificationClass,
            'title' => $this->title,
            'body' => $this->body,
            'scope' => $this->scope,
            'tokens' => $tokens,
        ];

        return hash('sha256', json_encode($data, \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES));
    }
}

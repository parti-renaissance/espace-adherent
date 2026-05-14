<?php

declare(strict_types=1);

namespace App\Entity;

use App\Firebase\PushNotificationStatusEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
class PushNotification
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    #[ORM\Column]
    public string $notificationClass;

    #[ORM\Column]
    public string $title;

    #[ORM\Column(type: 'text')]
    public string $body;

    #[ORM\Column(nullable: true)]
    public ?string $scope = null;

    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $data = null;

    #[ORM\Column(enumType: PushNotificationStatusEnum::class)]
    public PushNotificationStatusEnum $status = PushNotificationStatusEnum::PENDING;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $totalTokens = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $totalSuccess = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $totalFailed = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $chunksTotal = 0;

    #[ORM\Column(type: 'integer', options: ['unsigned' => true, 'default' => 0])]
    public int $chunksDelivered = 0;

    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'pushNotification')]
    public Collection $chunks;

    public function __construct(
        string $notificationClass,
        string $title,
        string $body,
        ?string $scope,
        ?array $data,
        int $chunksTotal,
    ) {
        $this->uuid = Uuid::v4();
        $this->notificationClass = $notificationClass;
        $this->title = $title;
        $this->body = $body;
        $this->scope = $scope;
        $this->data = $data;
        $this->chunksTotal = $chunksTotal;
        $this->chunks = new ArrayCollection();
    }

    public function __toString(): string
    {
        return \sprintf('[%s] %s', $this->createdAt?->format('d/m/Y') ?? '', $this->title);
    }

    public function recordChunkResult(int $tokensSent, int $tokensSuccess, int $tokensFailed): void
    {
        $this->totalTokens += $tokensSent;
        $this->totalSuccess += $tokensSuccess;
        $this->totalFailed += $tokensFailed;
        ++$this->chunksDelivered;

        $this->status = $this->chunksDelivered >= $this->chunksTotal
            ? PushNotificationStatusEnum::DELIVERED
            : PushNotificationStatusEnum::PARTIAL;
    }
}

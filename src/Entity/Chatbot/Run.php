<?php

declare(strict_types=1);

namespace App\Entity\Chatbot;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'chatbot_run')]
class Run
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use ExternalResourceTrait;

    public const STATUS_QUEUED = 'queued';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Thread::class)]
    public Thread $thread;

    #[ORM\Column]
    public string $status = self::STATUS_QUEUED;

    public function __construct(?Uuid $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::v4();
    }

    public function needRefresh(): bool
    {
        return \in_array($this->status, [
            self::STATUS_QUEUED,
            self::STATUS_IN_PROGRESS,
        ], true);
    }

    public function isInProgress(): bool
    {
        return self::STATUS_IN_PROGRESS === $this->status;
    }

    public function cancel(): void
    {
        $this->status = self::STATUS_CANCELLED;
    }
}

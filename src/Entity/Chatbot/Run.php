<?php

namespace App\Entity\Chatbot;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="chatbot_run")
 */
class Run
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use OpenAIResourceTrait;

    public const STATUS_QUEUED = 'queued';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    /**
     * @ORM\ManyToOne(targetEntity=Thread::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public Thread $thread;

    /**
     * @ORM\Column
     */
    public string $status = self::STATUS_QUEUED;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }
}

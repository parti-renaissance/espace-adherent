<?php

namespace App\Entity\Chatbot;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="chatbot_message")
 */
class Message
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use OpenAIResourceTrait;

    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';

    /**
     * @ORM\ManyToOne(targetEntity=Thread::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public Thread $thread;

    /**
     * @ORM\Column
     */
    public string $role;

    /**
     * @ORM\Column(type="text")
     */
    public string $content;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }
}

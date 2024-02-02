<?php

namespace App\Entity\Chatbot;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Chatbot\MessageRepository")
 * @ORM\Table(name="chatbot_message")
 */
class Message
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use ExternalResourceTrait;

    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';

    /**
     * @ORM\ManyToOne(targetEntity=Thread::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public Thread $thread;

    /**
     * @ORM\Column
     *
     * @Groups({"chatbot:read"})
     */
    public string $role;

    /**
     * @ORM\Column(type="text")
     *
     * @Groups({"chatbot:read"})
     */
    public string $content;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Groups({"chatbot:read"})
     */
    public \DateTimeInterface $date;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function isUserMessage(): bool
    {
        return self::ROLE_USER === $this->role;
    }
}

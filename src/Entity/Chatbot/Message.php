<?php

namespace App\Entity\Chatbot;

use App\Chatbot\Enum\MessageRoleEnum;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\OpenAI\Assistant;
use App\Entity\OpenAI\OpenAIResourceTrait;
use App\OpenAI\Model\MessageInterface;
use App\OpenAI\Model\ThreadInterface;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Chatbot\MessageRepository")
 * @ORM\Table(name="chatbot_message")
 */
class Message implements MessageInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use OpenAIResourceTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Thread::class, inversedBy="messages")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public Thread $thread;

    /**
     * @ORM\Column(enumType=MessageRoleEnum::class)
     *
     * @Groups({"chatbot:read"})
     */
    public ?MessageRoleEnum $role = null;

    /**
     * @ORM\Column(type="text")
     *
     * @Groups({"chatbot:read"})
     */
    public string $text;

    /**
     * @ORM\Column(type="json")
     *
     * @Groups({"chatbot:read"})
     */
    public array $entities = [];

    /**
     * @ORM\Column(type="datetime")
     *
     * @Groups({"chatbot:read"})
     */
    public \DateTimeInterface $date;

    /**
     * @ORM\ManyToOne(targetEntity=Assistant::class)
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?Assistant $assistant = null;

    /**
     * @ORM\ManyToOne(targetEntity=Run::class)
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?Run $run = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public static function create(
        Thread $thread,
        MessageRoleEnum $role,
        string $text,
        array $entities,
        \DateTimeInterface $date
    ): self {
        $message = new self();
        $message->thread = $thread;
        $message->role = $role;
        $message->text = $text;
        $message->entities = $entities;
        $message->date = $date;

        return $message;
    }

    public function isUserMessage(): bool
    {
        return MessageRoleEnum::USER === $this->role;
    }

    public function getThread(): ThreadInterface
    {
        return $this->thread;
    }

    public function getText(): string
    {
        return $this->text;
    }
}

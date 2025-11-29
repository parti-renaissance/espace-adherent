<?php

declare(strict_types=1);

namespace App\Entity\Chatbot;

use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Chatbot\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'chatbot_message')]
class Message
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use ExternalResourceTrait;

    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Thread::class, inversedBy: 'messages')]
    public Thread $thread;

    #[Groups(['chatbot:read'])]
    #[ORM\Column]
    public string $role;

    #[Groups(['chatbot:read'])]
    #[ORM\Column(type: 'text')]
    public string $content;

    #[Groups(['chatbot:read'])]
    #[ORM\Column(type: 'datetime')]
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

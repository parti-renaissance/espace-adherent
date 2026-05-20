<?php

declare(strict_types=1);

namespace App\Entity\Chatbot;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Chatbot\MessageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/threads/{uuid}/messages',
            uriVariables: [
                'uuid' => new Link(
                    fromProperty: 'messages',
                    fromClass: Thread::class,
                ),
            ],
            paginationItemsPerPage: 20,
            order: ['date' => 'DESC'],
        ),
    ],
    routePrefix: '/v3/ai',
    normalizationContext: ['groups' => ['chatbot:message_read']],
    security: "is_granted('REQUEST_SCOPE_GRANTED', 'chatbot')"
)]
#[ORM\Entity(repositoryClass: MessageRepository::class)]
#[ORM\Table(name: 'chatbot_message')]
class Message
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;

    public const ROLE_USER = 'user';
    public const ROLE_ASSISTANT = 'assistant';

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Thread::class, inversedBy: 'messages')]
    public Thread $thread;

    #[Groups(['chatbot:read', 'chatbot:message_read'])]
    #[ORM\Column]
    public string $role;

    #[Groups(['chatbot:read', 'chatbot:message_read'])]
    #[ORM\Column(type: 'text')]
    public string $content;

    #[Groups(['chatbot:read', 'chatbot:message_read'])]
    #[ORM\Column(type: 'datetime')]
    public \DateTimeInterface $date;

    public function __construct(?Uuid $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::v4();
    }

    public function isUserMessage(): bool
    {
        return self::ROLE_USER === $this->role;
    }
}

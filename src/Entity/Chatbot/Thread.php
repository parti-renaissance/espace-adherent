<?php

declare(strict_types=1);

namespace App\Entity\Chatbot;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Chatbot\ThreadRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ThreadRepository::class)]
#[ORM\Table(name: 'chatbot_thread')]
class Thread
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use ExternalResourceTrait;

    #[ORM\Column(nullable: true)]
    public ?string $telegramChatId = null;

    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Chatbot::class)]
    public Chatbot $chatbot;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\ManyToOne(targetEntity: Adherent::class)]
    public ?Adherent $adherent = null;

    /**
     * @var Message[]|Collection
     */
    #[Groups(['chatbot:read'])]
    #[ORM\OneToMany(mappedBy: 'thread', targetEntity: Message::class, cascade: ['all'], fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    public Collection $messages;

    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    #[ORM\OneToOne(targetEntity: Run::class, cascade: ['all'], fetch: 'EXTRA_LAZY')]
    public ?Run $currentRun = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->messages = new ArrayCollection();
    }

    public function startNewRun(): void
    {
        $run = new Run();
        $run->thread = $this;

        $this->currentRun = $run;
    }

    public function endCurrentRun(): void
    {
        $this->currentRun = null;
    }

    public function addAssistantMessage(string $content, \DateTimeInterface $date, string $externalId): Message
    {
        return $this->addMessage(Message::ROLE_ASSISTANT, $content, $date, $externalId);
    }

    public function addUserMessage(string $content, ?\DateTimeInterface $date = null): Message
    {
        return $this->addMessage(Message::ROLE_USER, $content, $date);
    }

    private function addMessage(string $role, string $content, ?\DateTimeInterface $date = null, ?string $externalId = null): Message
    {
        $message = new Message();
        $message->thread = $this;
        $message->role = $role;
        $message->content = $content;
        $message->date = $date ?? new \DateTimeImmutable('now');
        $message->externalId = $externalId;

        $this->messages->add($message);

        return $message;
    }

    public function hasMessageWithExternalId(string $externalId): bool
    {
        return null !== $this->messages->findFirst(
            static function (int $key, Message $message) use ($externalId): bool {
                return $externalId === $message->externalId;
            }
        );
    }

    public function getMessagesToInitialize(): Collection
    {
        return $this->messages->filter(
            static function (Message $message): bool {
                return $message->isUserMessage() && !$message->isInitialized();
            }
        );
    }

    #[Groups(['chatbot:read'])]
    public function getNeedRefresh(): bool
    {
        return null !== $this->currentRun;
    }
}

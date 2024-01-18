<?php

namespace App\Entity\Chatbot;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Chatbot\ThreadRepository")
 * @ORM\Table(name="chatbot_thread")
 */
class Thread
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use ExternalResourceTrait;

    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $telegramChatId = null;

    /**
     * @ORM\ManyToOne(targetEntity=Chatbot::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public Chatbot $chatbot;

    /**
     * @ORM\ManyToOne(targetEntity=Adherent::class)
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?Adherent $adherent = null;

    /**
     * @var Message[]|Collection
     *
     * @ORM\OneToMany(
     *     targetEntity=Message::class,
     *     mappedBy="thread",
     *     cascade={"all"},
     *     orphanRemoval=true,
     *     fetch="EXTRA_LAZY"
     * )
     *
     * @Groups({"chatbot:read"})
     */
    public Collection $messages;

    /**
     * @ORM\OneToOne(targetEntity=Run::class, cascade={"all"}, fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    public ?Run $currentRun = null;

    public function __construct(UuidInterface $uuid = null)
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

    public function addUserMessage(string $content, \DateTimeInterface $date = null): Message
    {
        return $this->addMessage(Message::ROLE_USER, $content, $date);
    }

    private function addMessage(string $role, string $content, \DateTimeInterface $date = null, string $externalId = null): Message
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

    /**
     * @Groups({"chatbot:read"})
     */
    public function getNeedRefresh(): bool
    {
        return null !== $this->currentRun;
    }
}

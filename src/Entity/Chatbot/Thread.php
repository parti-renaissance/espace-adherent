<?php

namespace App\Entity\Chatbot;

use App\Entity\Adherent;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\OpenAI\OpenAIResourceTrait;
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
    use OpenAIResourceTrait;

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

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
        $this->messages = new ArrayCollection();
    }

    public function getMessagesToInitializeOnOpenAi(): Collection
    {
        return $this->messages->filter(
            static function (Message $message): bool {
                return null !== $message->openAiId;
            }
        );
    }

    public function hasMessageWithOpenAiId(string $openAiId): bool
    {
        return null !== $this->messages->findFirst(
            static function (Message $message) use ($openAiId): bool {
                return $openAiId === $message->openAiId;
            }
        );
    }

    public function hasCurrentRun(): bool
    {
        return null !== $this->currentRun;
    }
}

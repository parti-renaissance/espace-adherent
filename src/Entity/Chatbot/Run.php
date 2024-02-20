<?php

namespace App\Entity\Chatbot;

use App\Chatbot\Enum\RunStatusEnum;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\OpenAI\OpenAIResourceTrait;
use App\OpenAI\Enum\RunStatusEnum as OpenAIRunStatusEnum;
use App\OpenAI\Model\RunInterface;
use App\OpenAI\Model\ThreadInterface;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Chatbot\RunRepository")
 * @ORM\Table(name="chatbot_run")
 */
class Run implements RunInterface
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use OpenAIResourceTrait;

    /**
     * @ORM\ManyToOne(targetEntity=Thread::class)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    public Thread $thread;

    /**
     * @ORM\Column(enumType=RunStatusEnum::class)
     */
    public RunStatusEnum $status = RunStatusEnum::QUEUED;

    /**
     * @ORM\Column(enumType=OpenAIRunStatusEnum::class, nullable=true)
     */
    public ?OpenAIRunStatusEnum $openAiStatus = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function updateOpenAiStatus(OpenAIRunStatusEnum $openAiStatus): void
    {
        $this->status = RunStatusEnum::fromOpenAI($openAiStatus);
        $this->openAiStatus = $openAiStatus;
    }

    public function needRefresh(): bool
    {
        return \in_array($this->status, RunStatusEnum::NEED_REFRESH, true);
    }

    public function isInProgress(): bool
    {
        return RunStatusEnum::IN_PROGRESS === $this->status;
    }

    public function isCompleted(): bool
    {
        return RunStatusEnum::COMPLETED === $this->status;
    }

    public function cancel(): void
    {
        $this->status = RunStatusEnum::CANCELLED;
        $this->openAiStatus = null;
    }

    public function getThread(): ThreadInterface
    {
        return $this->thread;
    }
}

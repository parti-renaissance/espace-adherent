<?php

namespace App\Entity\Chatbot;

use App\Chatbot\Enum\AssistantTypeEnum;
use App\Chatbot\Enum\ChatbotTypeEnum;
use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Entity\OpenAI\Assistant as OpenAIAssistant;
use App\Entity\TelegramBot;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Chatbot\ChatbotRepository")
 *
 * @UniqueEntity(fields={"name"})
 */
class Chatbot
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    /**
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank
     */
    public ?string $name = null;

    /**
     * @ORM\Column(enumType=ChatbotTypeEnum::class)
     *
     * @Assert\NotBlank
     * @Assert\Type(type=ChatbotTypeEnum::class)
     */
    public ?ChatbotTypeEnum $type = null;

    /**
     * @ORM\Column(enumType=AssistantTypeEnum::class)
     *
     * @Assert\NotBlank
     * @Assert\Type(type=AssistantTypeEnum::class)
     */
    public ?AssistantTypeEnum $assistantType = null;

    /**
     * @ORM\OneToOne(targetEntity=OpenAIAssistant::class, cascade={"all"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @Assert\Expression(expression="this.isOpenAIAssistant() and value")
     */
    public ?OpenAIAssistant $openAiAssistant = null;

    /**
     * @ORM\OneToOne(targetEntity=TelegramBot::class, cascade={"all"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @Assert\Expression(expression="this.isTelegramBot() and value")
     */
    public ?TelegramBot $telegramBot = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function isOpenAIAssistant(): bool
    {
        return AssistantTypeEnum::OPENAI === $this->assistantType;
    }

    public function getOpenAiAssistant(): ?OpenAIAssistant
    {
        return $this->openAiAssistant;
    }

    public function isTelegramBot(): bool
    {
        return ChatbotTypeEnum::TELEGRAM === $this->type;
    }

    public function getTelegramBot(): ?TelegramBot
    {
        return $this->telegramBot;
    }
}

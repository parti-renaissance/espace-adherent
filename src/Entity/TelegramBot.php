<?php

namespace App\Entity;

use App\Telegram\BotInterface;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TelegramBotRepository")
 * @ORM\Table(name="telegram_bot")
 *
 * @UniqueEntity(fields={"name"})
 * @UniqueEntity(fields={"apiToken"})
 * @UniqueEntity(fields={"secret"})
 */
class TelegramBot implements BotInterface
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
     * @ORM\Column(type="boolean", options={"default": false})
     */
    public bool $enabled = false;

    /**
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank
     */
    public ?string $apiToken = null;

    /**
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank
     */
    public ?string $secret = null;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Assert\All({
     *     @Assert\NotBlank,
     *     @Assert\Type("string")
     * })
     */
    public array $blacklistedIds = [];

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     *
     * @Assert\All({
     *     @Assert\NotBlank,
     *     @Assert\Type("string")
     * })
     */
    public array $whitelistedIds = [];

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getIdentifier(): string
    {
        return $this->uuid->toString();
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getApiToken(): string
    {
        return $this->apiToken;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function getBlacklistedChatIds(): array
    {
        return $this->blacklistedIds;
    }

    public function getWhitelistedChatIds(): array
    {
        return $this->whitelistedIds;
    }

    public function generateSecret(): void
    {
        $this->secret = Uuid::uuid4();
    }
}

<?php

namespace App\Entity\Chatbot;

use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\Repository\Chatbot\ChatbotRepository;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ChatbotRepository::class)]
#[UniqueEntity(fields: ['code'])]
class Chatbot
{
    use EntityIdentityTrait;
    use EntityTimestampableTrait;
    use EntityAdministratorBlameableTrait;

    #[ORM\Column(unique: true)]
    #[Assert\NotBlank]
    public ?string $code = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    public ?string $assistantId = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    public bool $enabled = false;

    #[ORM\Column(nullable: true)]
    public ?string $telegramBotApiToken = null;

    #[ORM\Column(nullable: true)]
    public ?string $telegramBotSecret = null;

    public function __construct(?UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString(): string
    {
        return (string) $this->code;
    }
}

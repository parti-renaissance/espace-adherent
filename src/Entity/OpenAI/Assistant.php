<?php

namespace App\Entity\OpenAI;

use App\Entity\EntityAdministratorBlameableTrait;
use App\Entity\EntityIdentityTrait;
use App\Entity\EntityTimestampableTrait;
use App\OpenAI\AssistantInterface;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OpenAI\AssistantRepository")
 * @ORM\Table(name="openai_assistant")
 *
 * @UniqueEntity(fields={"name"})
 * @UniqueEntity(fields={"identifier"})
 */
class Assistant implements AssistantInterface
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
     * @ORM\Column(unique=true)
     *
     * @Assert\NotBlank
     */
    public ?string $identifier = null;

    public function __construct(UuidInterface $uuid = null)
    {
        $this->uuid = $uuid ?? Uuid::uuid4();
    }

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}

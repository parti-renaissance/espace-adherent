<?php

namespace App\Entity\OpenAI;

use Doctrine\ORM\Mapping as ORM;

trait OpenAIResourceTrait
{
    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $openAiId = null;

    public function setOpenAiId(string $openAiId): void
    {
        $this->openAiId = $openAiId;
    }

    public function hasOpenAiId(): bool
    {
        return null !== $this->openAiId;
    }

    public function getOpenAiId(): ?string
    {
        return $this->openAiId;
    }
}

<?php

namespace App\Entity\OpenAI;

use Doctrine\ORM\Mapping as ORM;

trait OpenAIResourceTrait
{
    /**
     * @ORM\Column(nullable=true)
     */
    public ?string $openAiId = null;
}

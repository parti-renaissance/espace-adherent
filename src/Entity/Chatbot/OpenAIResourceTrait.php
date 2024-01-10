<?php

namespace App\Entity\Chatbot;

use Doctrine\ORM\Mapping as ORM;

trait OpenAIResourceTrait
{
    /**
     * @ORM\Column(unique=true, nullable=true)
     */
    public ?string $externalId = null;
}

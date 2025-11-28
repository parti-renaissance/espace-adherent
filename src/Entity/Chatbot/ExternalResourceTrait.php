<?php

declare(strict_types=1);

namespace App\Entity\Chatbot;

use Doctrine\ORM\Mapping as ORM;

trait ExternalResourceTrait
{
    #[ORM\Column(nullable: true)]
    public ?string $externalId = null;

    public function isInitialized(): bool
    {
        return null !== $this->externalId;
    }
}

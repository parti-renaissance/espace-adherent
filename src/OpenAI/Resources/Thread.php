<?php

namespace App\OpenAI\Resources;

use Symfony\Component\Serializer\Annotation\Groups;

class Thread
{
    public string $id;

    /**
     * @Groups({"chatbot_read"})
     */
    public \DateTimeInterface $createdAt;

    /**
     * @Groups({"chatbot_read"})
     */
    public ?string $lastRunStatus;

    /**
     * @Groups({"chatbot_read"})
     */
    public array $messages;

    public function __construct(
        string $id,
        \DateTimeInterface $createdAt,
        string $lastRunStatus = null,
        array $messages = []
    ) {
        $this->id = $id;
        $this->createdAt = $createdAt;
        $this->lastRunStatus = $lastRunStatus;
        $this->messages = $messages;
    }
}

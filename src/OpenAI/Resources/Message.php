<?php

namespace App\OpenAI\Resources;

use Symfony\Component\Serializer\Annotation\Groups;

class Message
{
    public string $id;

    /**
     * @Groups({"chatbot_read"})
     */
    public \DateTimeInterface $date;

    /**
     * @Groups({"chatbot_read"})
     */
    public string $role;

    /**
     * @Groups({"chatbot_read"})
     */
    public string $content;

    public function __construct(
        string $id,
        \DateTimeInterface $date,
        string $role,
        string $content
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->role = $role;
        $this->content = $content;
    }
}

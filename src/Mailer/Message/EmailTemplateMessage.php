<?php

namespace App\Mailer\Message;

use App\Entity\Email\TransactionalEmailTemplate;
use Ramsey\Uuid\Uuid;

class EmailTemplateMessage extends Message
{
    public static function create(TransactionalEmailTemplate $template, string $recipientEmail): self
    {
        return new self(
            Uuid::uuid4(),
            $recipientEmail,
            $recipientEmail,
            $template->subject,
            [],
            [],
            null,
            null,
            [],
            $template
        );
    }
}

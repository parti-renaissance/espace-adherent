<?php

declare(strict_types=1);

namespace App\Mailer\Message;

use App\Entity\Email\TransactionalEmailTemplate;
use Ramsey\Uuid\Uuid;

class EmailTemplateMessage extends Message
{
    public static function create(TransactionalEmailTemplate $template, array $recipientEmails): self
    {
        $email = array_shift($recipientEmails);

        $message = new self(
            Uuid::uuid4(),
            $email,
            $email,
            $template->subject ?? '',
            [],
            [],
            null,
            null,
            [],
            $template
        );

        foreach ($recipientEmails as $email) {
            $message->addRecipient($email);
        }

        return $message;
    }
}

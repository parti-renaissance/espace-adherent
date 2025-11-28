<?php

declare(strict_types=1);

namespace App\AdherentMessage;

enum MailchimpStatusEnum: string
{
    case Save = 'save';
    case Paused = 'paused';
    case Schedule = 'schedule';
    case Sending = 'sending';
    case Sent = 'sent';
    case Canceled = 'canceled';
    case Canceling = 'canceling';
    case Archived = 'archived';
}

<?php

declare(strict_types=1);

namespace App\Mailchimp\Campaign\Audience;

enum BlockReasonEnum: string
{
    case Empty = 'empty';
    case MailchimpUnavailable = 'mailchimp_unavailable';
    case AlreadySent = 'already_sent';
    case Conflict = 'conflict';
    case PreparationErrors = 'preparation_errors';
}

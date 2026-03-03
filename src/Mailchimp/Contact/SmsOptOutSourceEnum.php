<?php

declare(strict_types=1);

namespace App\Mailchimp\Contact;

enum SmsOptOutSourceEnum: string
{
    case Mailchimp = 'mailchimp';
}

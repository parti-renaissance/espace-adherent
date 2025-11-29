<?php

declare(strict_types=1);

namespace App\Mailchimp\Contact;

interface MailchimpCleanableContactInterface
{
    public function clean(): void;
}

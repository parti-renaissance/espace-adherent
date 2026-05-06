<?php

declare(strict_types=1);

namespace App\Mailchimp\Concurrency;

interface MailchimpSlot
{
    public function release(): void;
}

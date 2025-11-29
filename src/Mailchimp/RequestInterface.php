<?php

declare(strict_types=1);

namespace App\Mailchimp;

interface RequestInterface
{
    public function toArray(): array;
}

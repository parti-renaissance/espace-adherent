<?php

declare(strict_types=1);

namespace App\Mailchimp\Synchronisation;

interface QueuePriorityLevelEnum
{
    public const QUEUE_NAME = 'mailchimp_batch';

    public const int LOW = 1;
    public const int MEDIUM = 50;
    public const int HIGH = 100;
}

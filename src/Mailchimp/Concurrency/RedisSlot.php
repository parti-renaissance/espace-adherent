<?php

declare(strict_types=1);

namespace App\Mailchimp\Concurrency;

use Symfony\Component\Lock\LockInterface;

class RedisSlot implements MailchimpSlot
{
    private bool $released = false;

    public function __construct(
        private readonly LockInterface $lock,
        private readonly int $slotIndex,
    ) {
    }

    public function release(): void
    {
        if ($this->released) {
            return;
        }
        $this->released = true;
        $this->lock->release();
    }

    public function getSlotIndex(): int
    {
        return $this->slotIndex;
    }

    public function __destruct()
    {
        $this->release();
    }
}

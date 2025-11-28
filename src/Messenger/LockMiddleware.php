<?php

declare(strict_types=1);

namespace App\Messenger;

use App\Messenger\Message\LockableMessageInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

class LockMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly LockFactory $lockFactory)
    {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();

        if (!$message instanceof LockableMessageInterface || empty($envelope->all(ReceivedStamp::class))) {
            return $stack->next()->handle($envelope, $stack);
        }

        $lock = $this->lockFactory->createLock($message->getLockKey(), $message->getLockTtl());

        $lock->acquire($message->isLockBlocking());

        try {
            return $stack->next()->handle($envelope, $stack);
        } finally {
            $lock->release();
        }
    }
}

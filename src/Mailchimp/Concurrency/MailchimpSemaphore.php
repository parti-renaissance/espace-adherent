<?php

declare(strict_types=1);

namespace App\Mailchimp\Concurrency;

use App\Mailchimp\Concurrency\Exception\MailchimpConcurrencyTimeoutException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Lock\LockFactory;

class MailchimpSemaphore
{
    public const int SLOT_COUNT = 10;
    public const int TTL_SECONDS = 90;
    public const int ACQUIRE_TIMEOUT_MS = 10_000;

    private const array BACKOFF_STEPS_MS = [100, 250, 500, 1_000, 2_000];

    private LoggerInterface $logger;

    public function __construct(
        private readonly LockFactory $lockFactory,
        ?LoggerInterface $logger = null,
    ) {
        $this->logger = $logger ?? new NullLogger();
    }

    public function acquire(): MailchimpSlot
    {
        $startMs = $this->nowMs();
        $stepIndex = 0;
        $attempt = 0;

        while (true) {
            ++$attempt;
            try {
                $slot = $this->tryAcquireRandomSlot();
            } catch (\Throwable $e) {
                $this->logger->warning('Mailchimp semaphore failed to reach Redis — fail-open', [
                    'exception' => $e,
                    'attempt' => $attempt,
                ]);

                return new NullSlot();
            }

            if (null !== $slot) {
                return $slot;
            }

            $elapsedMs = $this->nowMs() - $startMs;
            if ($elapsedMs >= self::ACQUIRE_TIMEOUT_MS) {
                throw new MailchimpConcurrencyTimeoutException(self::ACQUIRE_TIMEOUT_MS);
            }

            $backoffMs = self::BACKOFF_STEPS_MS[min($stepIndex, \count(self::BACKOFF_STEPS_MS) - 1)];
            $remainingMs = self::ACQUIRE_TIMEOUT_MS - $elapsedMs;
            $sleepMs = min($backoffMs, $remainingMs);
            $this->sleep($sleepMs);
            ++$stepIndex;
        }
    }

    private function tryAcquireRandomSlot(): ?MailchimpSlot
    {
        $slotIndex = random_int(0, self::SLOT_COUNT - 1);
        $lock = $this->lockFactory->createLock(\sprintf('mailchimp.slot.%d', $slotIndex), (float) self::TTL_SECONDS);

        if (!$lock->acquire(false)) {
            return null;
        }

        return new RedisSlot($lock, $slotIndex);
    }

    /**
     * Test seam: lets tests short-circuit the actual sleep.
     */
    protected function sleep(int $milliseconds): void
    {
        usleep($milliseconds * 1000);
    }

    /**
     * Test seam: lets tests virtualize the clock.
     */
    protected function nowMs(): int
    {
        return (int) (microtime(true) * 1000);
    }
}

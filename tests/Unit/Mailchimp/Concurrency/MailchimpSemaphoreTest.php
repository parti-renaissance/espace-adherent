<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Concurrency;

use App\Mailchimp\Concurrency\Exception\MailchimpConcurrencyTimeoutException;
use App\Mailchimp\Concurrency\MailchimpSemaphore;
use App\Mailchimp\Concurrency\MailchimpSlot;
use App\Mailchimp\Concurrency\NullSlot;
use App\Mailchimp\Concurrency\Priority;
use App\Mailchimp\Concurrency\RedisSlot;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\SharedLockInterface;

final class MailchimpSemaphoreTest extends TestCase
{
    public function testAcquireWhenSlotAvailableReturnsRedisSlot(): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock
            ->expects(self::once())
            ->method('acquire')
            ->with(false)
            ->willReturn(true);

        $lockFactory = $this->createMock(LockFactory::class);
        $lockFactory
            ->expects(self::once())
            ->method('createLock')
            ->with(self::stringStartsWith('mailchimp.slot.'), (float) MailchimpSemaphore::TTL_SECONDS)
            ->willReturn($lock);

        $semaphore = new TestableMailchimpSemaphore($lockFactory);
        $slot = $semaphore->acquire();

        self::assertInstanceOf(RedisSlot::class, $slot);
        self::assertGreaterThanOrEqual(0, $slot->getSlotIndex());
        self::assertLessThan(MailchimpSemaphore::SLOT_COUNT, $slot->getSlotIndex());
    }

    public function testAcquireWhenAllSlotsLockedThenOneFreedRetriesAndAcquires(): void
    {
        $lockBusy = $this->createMock(SharedLockInterface::class);
        $lockBusy
            ->expects(self::atLeastOnce())
            ->method('acquire')
            ->with(false)
            ->willReturn(false);

        $lockFree = $this->createMock(SharedLockInterface::class);
        $lockFree
            ->expects(self::once())
            ->method('acquire')
            ->with(false)
            ->willReturn(true);

        $lockFactory = $this->createMock(LockFactory::class);
        $lockFactory
            ->expects(self::exactly(3))
            ->method('createLock')
            ->with(self::stringStartsWith('mailchimp.slot.'), (float) MailchimpSemaphore::TTL_SECONDS)
            ->willReturnOnConsecutiveCalls($lockBusy, $lockBusy, $lockFree);

        $semaphore = new TestableMailchimpSemaphore($lockFactory);
        $slot = $semaphore->acquire();

        self::assertInstanceOf(RedisSlot::class, $slot);
        self::assertGreaterThanOrEqual(2, $semaphore->getSleepCount(), 'Backoff sleep should be invoked at least twice before success');
    }

    public function testAcquireWhenTimeoutExceededThrowsTimeoutException(): void
    {
        $lockBusy = $this->createMock(SharedLockInterface::class);
        $lockBusy
            ->expects(self::atLeastOnce())
            ->method('acquire')
            ->with(false)
            ->willReturn(false);

        $lockFactory = $this->createMock(LockFactory::class);
        $lockFactory
            ->expects(self::atLeastOnce())
            ->method('createLock')
            ->with(self::stringStartsWith('mailchimp.slot.'), (float) MailchimpSemaphore::TTL_SECONDS)
            ->willReturn($lockBusy);

        $semaphore = new TestableMailchimpSemaphore($lockFactory);

        $this->expectException(MailchimpConcurrencyTimeoutException::class);
        $this->expectExceptionMessage('120000ms');

        $semaphore->acquire();
    }

    public function testAcquireWhenRedisDownReturnsNullSlot(): void
    {
        $lockFactory = $this->createMock(LockFactory::class);
        $lockFactory
            ->expects(self::once())
            ->method('createLock')
            ->with(self::stringStartsWith('mailchimp.slot.'), (float) MailchimpSemaphore::TTL_SECONDS)
            ->willThrowException(new LockAcquiringException('Redis unreachable'));

        $semaphore = new TestableMailchimpSemaphore($lockFactory);
        $slot = $semaphore->acquire();

        self::assertInstanceOf(NullSlot::class, $slot);
    }

    public function testAcquireWhenLockAcquireThrowsReturnsNullSlot(): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock
            ->expects(self::once())
            ->method('acquire')
            ->with(false)
            ->willThrowException(new LockAcquiringException('Redis dropped during acquire'));

        $lockFactory = $this->createMock(LockFactory::class);
        $lockFactory
            ->expects(self::once())
            ->method('createLock')
            ->with(self::stringStartsWith('mailchimp.slot.'), (float) MailchimpSemaphore::TTL_SECONDS)
            ->willReturn($lock);

        $semaphore = new TestableMailchimpSemaphore($lockFactory);
        $slot = $semaphore->acquire();

        self::assertInstanceOf(NullSlot::class, $slot);
    }

    public function testRedisSlotReleaseIsIdempotent(): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock
            ->expects(self::once())
            ->method('release');

        $slot = new RedisSlot($lock, 3);
        $slot->release();
        $slot->release();
        $slot->release();

        self::assertSame(3, $slot->getSlotIndex());
    }

    public function testNullSlotReleaseIsNoOp(): void
    {
        $slot = new NullSlot();
        $slot->release();
        $slot->release();

        self::assertInstanceOf(MailchimpSlot::class, $slot);
    }

    public function testLowPriorityNeverPicksReservedSlots(): void
    {
        $lock = $this->createMock(SharedLockInterface::class);
        $lock
            ->expects(self::exactly(50))
            ->method('acquire')
            ->with(false)
            ->willReturn(true)
        ;

        $pickedKeys = [];
        $lockFactory = $this->createMock(LockFactory::class);
        $lockFactory
            ->expects(self::atLeast(50))
            ->method('createLock')
            ->willReturnCallback(function (string $key) use (&$pickedKeys, $lock): SharedLockInterface {
                $pickedKeys[] = $key;

                return $lock;
            });

        $semaphore = new TestableMailchimpSemaphore($lockFactory);

        for ($i = 0; $i < 50; ++$i) {
            $semaphore->acquire(Priority::Low);
        }

        $reservedKeys = [];
        for ($i = MailchimpSemaphore::LOW_PRIORITY_SLOT_LIMIT; $i < MailchimpSemaphore::SLOT_COUNT; ++$i) {
            $reservedKeys[] = \sprintf('mailchimp.slot.%d', $i);
        }

        foreach ($pickedKeys as $key) {
            self::assertNotContains($key, $reservedKeys, 'Low priority must not pick reserved slots');
        }
    }

    public function testHighPriorityCanPickAnySlot(): void
    {
        $allReachableSlots = [];
        for ($i = 0; $i < MailchimpSemaphore::SLOT_COUNT; ++$i) {
            $allReachableSlots[\sprintf('mailchimp.slot.%d', $i)] = false;
        }

        $lock = $this->createMock(SharedLockInterface::class);
        $lock
            ->expects(self::exactly(200))
            ->method('acquire')
            ->with(false)
            ->willReturn(true)
        ;

        $lockFactory = $this->createStub(LockFactory::class);
        $lockFactory
            ->method('createLock')
            ->willReturnCallback(function (string $key) use (&$allReachableSlots, $lock): SharedLockInterface {
                if (\array_key_exists($key, $allReachableSlots)) {
                    $allReachableSlots[$key] = true;
                }

                return $lock;
            });

        $semaphore = new TestableMailchimpSemaphore($lockFactory);

        // 200 acquires gives ~99.99% chance of covering all 10 slots if random is uniform.
        for ($i = 0; $i < 200; ++$i) {
            $semaphore->acquire(Priority::High);
        }

        foreach ($allReachableSlots as $key => $reached) {
            self::assertTrue($reached, \sprintf('High priority should be able to reach %s', $key));
        }
    }
}

/**
 * Test double: virtualizes the clock and counts sleeps.
 */
final class TestableMailchimpSemaphore extends MailchimpSemaphore
{
    private int $sleepCount = 0;
    private int $virtualNowMs = 0;

    public function getSleepCount(): int
    {
        return $this->sleepCount;
    }

    protected function sleep(int $milliseconds): void
    {
        ++$this->sleepCount;
        $this->virtualNowMs += $milliseconds;
    }

    protected function nowMs(): int
    {
        return $this->virtualNowMs;
    }
}

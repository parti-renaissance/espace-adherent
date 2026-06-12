<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Messenger;

use App\Mailchimp\Concurrency\MailchimpPriorityContext;
use App\Mailchimp\Concurrency\MailchimpSemaphore;
use App\Mailchimp\Concurrency\MailchimpSlot;
use App\Mailchimp\Concurrency\Priority;
use App\Mailchimp\Messenger\MailchimpHandlerMiddleware;
use App\Mailchimp\Synchronisation\QueuePriorityLevelEnum;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

final class MailchimpHandlerMiddlewareTest extends TestCase
{
    public function testBatchTransportAcquiresLowSlotAndExposesItDuringHandling(): void
    {
        $slot = $this->createMock(MailchimpSlot::class);
        $slot->expects(self::once())->method('release');

        $semaphore = $this->createMock(MailchimpSemaphore::class);
        $semaphore->expects(self::once())
            ->method('acquire')
            ->with(Priority::Low)
            ->willReturn($slot)
        ;

        $context = new MailchimpPriorityContext();
        $middleware = new MailchimpHandlerMiddleware($context, $semaphore);

        $observedPriority = null;
        $observedSlot = null;
        $envelope = new Envelope(new \stdClass(), [new ReceivedStamp(QueuePriorityLevelEnum::QUEUE_NAME)]);

        $middleware->handle($envelope, $this->buildStack(function () use ($context, $slot, &$observedPriority, &$observedSlot): void {
            $observedPriority = $context->getPriority();
            $observedSlot = $context->getHeldSlot();
            self::assertTrue($context->hasHeldSlot());
            self::assertSame($slot, $observedSlot);
        }));

        self::assertSame(Priority::Low, $observedPriority);
        self::assertNull($context->getHeldSlot(), 'Held slot must be cleared after handling');
        self::assertFalse($context->hasHeldSlot());
    }

    public function testSyncTransportAcquiresHighSlot(): void
    {
        $slot = $this->createMock(MailchimpSlot::class);
        $slot->expects(self::once())->method('release');

        $semaphore = $this->createMock(MailchimpSemaphore::class);
        $semaphore->expects(self::once())
            ->method('acquire')
            ->with(Priority::High)
            ->willReturn($slot)
        ;

        $context = new MailchimpPriorityContext();
        $middleware = new MailchimpHandlerMiddleware($context, $semaphore);
        $envelope = new Envelope(new \stdClass(), [new ReceivedStamp('mailchimp_sync')]);

        $middleware->handle($envelope, $this->buildStack(function () use ($context): void {
            self::assertSame(Priority::High, $context->getPriority());
        }));
    }

    public function testCampaignTransportAcquiresHighSlot(): void
    {
        $slot = $this->createMock(MailchimpSlot::class);
        $slot->expects(self::once())->method('release');

        $semaphore = $this->createMock(MailchimpSemaphore::class);
        $semaphore->expects(self::once())
            ->method('acquire')
            ->with(Priority::High)
            ->willReturn($slot)
        ;

        $context = new MailchimpPriorityContext();
        $middleware = new MailchimpHandlerMiddleware($context, $semaphore);
        $envelope = new Envelope(new \stdClass(), [new ReceivedStamp('mailchimp_campaign')]);

        $middleware->handle($envelope, $this->buildStack(static function (): void {}));
    }

    public function testContextIsRestoredEvenWhenHandlerThrows(): void
    {
        $slot = $this->createMock(MailchimpSlot::class);
        $slot->expects(self::once())->method('release');

        $semaphore = $this->createStub(MailchimpSemaphore::class);
        $semaphore->method('acquire')->willReturn($slot);

        $context = new MailchimpPriorityContext();
        $context->setPriority(Priority::High);

        $middleware = new MailchimpHandlerMiddleware($context, $semaphore);
        $envelope = new Envelope(new \stdClass(), [new ReceivedStamp(QueuePriorityLevelEnum::QUEUE_NAME)]);

        try {
            $middleware->handle($envelope, $this->buildStack(static function (): void {
                throw new \RuntimeException('handler error');
            }));
            self::fail('Expected RuntimeException');
        } catch (\RuntimeException) {
            // expected
        }

        self::assertSame(Priority::High, $context->getPriority());
        self::assertNull($context->getHeldSlot());
    }

    public function testEnvelopeWithoutReceivedStampSkipsAcquire(): void
    {
        $semaphore = $this->createMock(MailchimpSemaphore::class);
        $semaphore->expects(self::never())->method('acquire');

        $context = new MailchimpPriorityContext();
        $middleware = new MailchimpHandlerMiddleware($context, $semaphore);
        $envelope = new Envelope(new \stdClass());

        $middleware->handle($envelope, $this->buildStack(function () use ($context): void {
            self::assertFalse($context->hasHeldSlot());
        }));
    }

    public function testNonMailchimpTransportSkipsAcquire(): void
    {
        $semaphore = $this->createMock(MailchimpSemaphore::class);
        $semaphore->expects(self::never())->method('acquire');

        $context = new MailchimpPriorityContext();
        $middleware = new MailchimpHandlerMiddleware($context, $semaphore);
        $envelope = new Envelope(new \stdClass(), [new ReceivedStamp('notification')]);

        $middleware->handle($envelope, $this->buildStack(function () use ($context): void {
            self::assertFalse($context->hasHeldSlot(), 'Non-Mailchimp transports must not consume a slot');
        }));
    }

    private function buildStack(\Closure $observe): StackInterface
    {
        $next = new class($observe) implements MiddlewareInterface {
            public function __construct(private readonly \Closure $observe)
            {
            }

            public function handle(Envelope $envelope, StackInterface $stack): Envelope
            {
                ($this->observe)();

                return $envelope;
            }
        };

        return new class($next) implements StackInterface {
            public function __construct(private readonly MiddlewareInterface $next)
            {
            }

            public function next(): MiddlewareInterface
            {
                return $this->next;
            }
        };
    }
}

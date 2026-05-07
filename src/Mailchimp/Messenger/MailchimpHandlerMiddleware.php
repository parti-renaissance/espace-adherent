<?php

declare(strict_types=1);

namespace App\Mailchimp\Messenger;

use App\Mailchimp\Concurrency\MailchimpPriorityContext;
use App\Mailchimp\Concurrency\MailchimpSemaphore;
use App\Mailchimp\Concurrency\Priority;
use App\Mailchimp\Synchronisation\QueuePriorityLevelEnum;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

/**
 * Sets the Mailchimp execution context (priority + held slot) around each handled
 * Mailchimp Messenger message. Acquires one semaphore slot at message start and
 * holds it until the handler returns, so all HTTP calls within the handler reuse
 * that slot instead of churning acquire/release between them.
 *
 * Non-Mailchimp messages and dispatch-path envelopes (no ReceivedStamp) pass
 * through unchanged.
 */
class MailchimpHandlerMiddleware implements MiddlewareInterface
{
    /** Any transport with this prefix is considered Mailchimp-bound and goes through the slot lifecycle. */
    private const string MAILCHIMP_TRANSPORT_PREFIX = 'mailchimp_';

    public function __construct(
        private readonly MailchimpPriorityContext $context,
        private readonly MailchimpSemaphore $semaphore,
    ) {
    }

    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $receivedStamp = $envelope->last(ReceivedStamp::class);
        if (null === $receivedStamp) {
            return $stack->next()->handle($envelope, $stack);
        }

        $transport = $receivedStamp->getTransportName();
        if (!str_starts_with($transport, self::MAILCHIMP_TRANSPORT_PREFIX)) {
            return $stack->next()->handle($envelope, $stack);
        }

        $priority = QueuePriorityLevelEnum::QUEUE_NAME === $transport
            ? Priority::Low
            : Priority::High;

        $previousPriority = $this->context->getPriority();
        $previousSlot = $this->context->getHeldSlot();

        $this->context->setPriority($priority);
        $slot = $this->semaphore->acquire($priority);
        $this->context->setHeldSlot($slot);

        try {
            return $stack->next()->handle($envelope, $stack);
        } finally {
            $slot->release();
            $this->context->setHeldSlot($previousSlot);
            $this->context->setPriority($previousPriority);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Mailchimp\Concurrency\EventSubscriber;

use App\Mailchimp\Concurrency\Exception\MailchimpConcurrencyTimeoutException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

/**
 * Reports MailchimpConcurrencyTimeoutException to Sentry only when the message
 * has exhausted its Messenger retries and is about to land in the failed queue.
 *
 * The exception itself is in SentryIgnoredExceptionInterface to keep the dashboard
 * clean during normal back-pressure (each retry would otherwise fire). This
 * subscriber re-surfaces it via an explicit logger->error() at the very last
 * moment, where it does represent a real systemic incident worth investigating.
 */
class MailchimpConcurrencyTimeoutFailureSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [WorkerMessageFailedEvent::class => 'onMessageFailed'];
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        if ($event->willRetry()) {
            return;
        }

        $timeoutException = $this->findConcurrencyTimeout($event->getThrowable());
        if (null === $timeoutException) {
            return;
        }

        $this->logger->error(
            'Mailchimp concurrency timeout: message dropped to failed queue after retries exhausted.',
            [
                'transport' => $event->getReceiverName(),
                'message_class' => $event->getEnvelope()->getMessage()::class,
                'exception' => $timeoutException,
            ],
        );
    }

    /**
     * Walks the cause chain — Messenger wraps handler exceptions in HandlerFailedException
     * (and possibly more layers), so a flat instanceof check would miss it.
     */
    private function findConcurrencyTimeout(\Throwable $throwable): ?MailchimpConcurrencyTimeoutException
    {
        $current = $throwable;
        while (null !== $current) {
            if ($current instanceof MailchimpConcurrencyTimeoutException) {
                return $current;
            }
            $current = $current->getPrevious();
        }

        return null;
    }
}

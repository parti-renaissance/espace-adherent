<?php

declare(strict_types=1);

namespace Tests\App\Unit\Mailchimp\Concurrency\EventSubscriber;

use App\Mailchimp\Concurrency\EventSubscriber\MailchimpConcurrencyTimeoutFailureSubscriber;
use App\Mailchimp\Concurrency\Exception\MailchimpConcurrencyTimeoutException;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

final class MailchimpConcurrencyTimeoutFailureSubscriberTest extends TestCase
{
    public function testLogsErrorWhenRetriesExhaustedAndExceptionIsTimeout(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())
            ->method('error')
            ->with(
                self::stringContains('failed queue after retries exhausted'),
                self::callback(function (array $context): bool {
                    return 'mailchimp_batch' === $context['transport']
                        && \stdClass::class === $context['message_class']
                        && $context['exception'] instanceof MailchimpConcurrencyTimeoutException;
                }),
            )
        ;

        $event = $this->buildEvent(new MailchimpConcurrencyTimeoutException(120_000));
        new MailchimpConcurrencyTimeoutFailureSubscriber($logger)->onMessageFailed($event);
    }

    public function testDoesNothingWhenWillRetry(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $event = $this->buildEvent(new MailchimpConcurrencyTimeoutException(120_000));
        $event->setForRetry();

        new MailchimpConcurrencyTimeoutFailureSubscriber($logger)->onMessageFailed($event);
    }

    public function testDoesNothingForUnrelatedException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::never())->method('error');

        $event = $this->buildEvent(new \RuntimeException('unrelated'));

        new MailchimpConcurrencyTimeoutFailureSubscriber($logger)->onMessageFailed($event);
    }

    public function testUnwrapsHandlerFailedException(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('error');

        $inner = new MailchimpConcurrencyTimeoutException(120_000);
        $wrapped = new HandlerFailedException(new Envelope(new \stdClass()), [$inner]);

        $event = $this->buildEvent($wrapped);

        new MailchimpConcurrencyTimeoutFailureSubscriber($logger)->onMessageFailed($event);
    }

    private function buildEvent(\Throwable $throwable): WorkerMessageFailedEvent
    {
        return new WorkerMessageFailedEvent(
            new Envelope(new \stdClass()),
            'mailchimp_batch',
            $throwable,
        );
    }
}

<?php

namespace App\Messenger\Listeners;

use PhpAmqpLib\Exception\AMQPExceptionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

class StopMessengerWorkerListener implements EventSubscriberInterface
{
    private $run = true;

    public static function getSubscribedEvents()
    {
        return [
            WorkerMessageFailedEvent::class => 'onMessageFailed',
            WorkerRunningEvent::class => 'onWorkerRunning',
        ];
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $throwable = $event->getThrowable();

        if (!$throwable instanceof HandlerFailedException) {
            return;
        }

        foreach ($throwable->getNestedExceptions() as $exception) {
            if ($exception instanceof AMQPExceptionInterface) {
                $this->run = false;

                return;
            }
        }
    }

    public function onWorkerRunning(WorkerRunningEvent $event): void
    {
        if (!$event->isWorkerIdle() && !$this->run) {
            $this->run = true;
            $event->getWorker()->stop();
        }
    }
}

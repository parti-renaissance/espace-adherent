<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Handler;

use App\Ses\Webhook\Command\ProcessSesEventCommand;
use App\Ses\Webhook\Command\RecordSesRawEventCommand;
use App\Ses\Webhook\SesEventRecorder;
use App\Ses\Webhook\SesRawEventExtractor;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class RecordSesRawEventHandler
{
    public function __construct(
        private readonly SesRawEventExtractor $extractor,
        private readonly SesEventRecorder $recorder,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function __invoke(RecordSesRawEventCommand $command): void
    {
        $data = $this->extractor->extract($command->payload);
        $this->recorder->record($data, $command->receivedAt);

        if ('' !== $data->snsMessageId) {
            $this->bus->dispatch(new ProcessSesEventCommand($data->snsMessageId));
        }
    }
}

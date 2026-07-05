<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

use App\Ses\Webhook\Processor\SesEventProcessorInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class SesEventDispatcher
{
    /**
     * @param iterable<SesEventProcessorInterface> $processors
     */
    public function __construct(
        private readonly SesPayloadReader $reader,
        #[AutowireIterator('app.ses_event_processor')]
        private readonly iterable $processors = [],
    ) {
    }

    public function dispatch(array $payload): void
    {
        $decoded = $this->reader->decode($payload);
        $type = SesEventType::fromDecodedEvent($decoded);
        if (null === $type) {
            return;
        }

        $directNotification = !isset($decoded['eventType']);

        foreach ($this->processors as $processor) {
            if (!$processor->supports($type)) {
                continue;
            }

            if ($directNotification && !$processor->supportsDirectNotification()) {
                continue;
            }

            $processor->process($payload);
        }
    }
}

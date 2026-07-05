<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Processor;

use App\Ses\Webhook\AttributableSesEvent;
use App\Ses\Webhook\AttributableSesEventParser;
use App\Ses\Webhook\SesEventTarget;
use App\Ses\Webhook\SesEventTargetResolver;

abstract class AbstractAttributableSesEventProcessor implements SesEventProcessorInterface
{
    public function __construct(
        private readonly AttributableSesEventParser $parser,
        private readonly SesEventTargetResolver $resolver,
    ) {
    }

    public function supportsDirectNotification(): bool
    {
        return false;
    }

    public function process(array $payload): void
    {
        $event = $this->parser->parse($payload);
        if (null === $event) {
            return;
        }

        $target = $this->resolver->resolve($event->campaignUuid, $event->adherentUuid);
        if (null === $target) {
            return;
        }

        $this->attribute($target, $event);
    }

    abstract protected function attribute(SesEventTarget $target, AttributableSesEvent $event): void;
}

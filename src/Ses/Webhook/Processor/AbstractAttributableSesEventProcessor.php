<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Processor;

use App\Ses\Campaign\Reconciliation\SendErroredRowReconciler;
use App\Ses\Webhook\AttributableSesEvent;
use App\Ses\Webhook\AttributableSesEventParser;
use App\Ses\Webhook\SesEventTarget;
use App\Ses\Webhook\SesEventTargetResolver;

abstract class AbstractAttributableSesEventProcessor implements SesEventProcessorInterface
{
    public function __construct(
        private readonly AttributableSesEventParser $parser,
        private readonly SesEventTargetResolver $resolver,
        private readonly SendErroredRowReconciler $reconciler,
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

        // An event on this member is proof its send actually happened. If the row had been quarantined on an
        // ambiguous send failure (SendErrored), that proof lets it leave quarantine right now — the timed
        // reconciliation stays a mere fallback, so a delayed message lost with the broker can no longer cost us
        // the promotion. A no-op for the vast majority of events: only a quarantined row is ever promoted, and
        // only by an event that stamps a proof column (an open or a click alone proves nothing about the send).
        $this->reconciler->promoteForMember($target->messageId, $target->adherentId);
    }

    abstract protected function attribute(SesEventTarget $target, AttributableSesEvent $event): void;
}

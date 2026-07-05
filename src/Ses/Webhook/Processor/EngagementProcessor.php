<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Processor;

use App\AdherentMessage\Stats\EmailAppHitWriter;
use App\Ses\Webhook\AttributableSesEvent;
use App\Ses\Webhook\SesEngagementEvent;
use App\Ses\Webhook\SesEngagementParser;
use App\Ses\Webhook\SesEngagementType;
use App\Ses\Webhook\SesEventTarget;
use App\Ses\Webhook\SesEventTargetResolver;
use App\Ses\Webhook\SesEventType;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.ses_event_processor')]
class EngagementProcessor extends AbstractAttributableSesEventProcessor
{
    public function __construct(
        SesEngagementParser $parser,
        SesEventTargetResolver $resolver,
        private readonly EmailAppHitWriter $appHitWriter,
    ) {
        parent::__construct($parser, $resolver);
    }

    public function supports(SesEventType $type): bool
    {
        return SesEventType::Open === $type || SesEventType::Click === $type;
    }

    protected function attribute(SesEventTarget $target, AttributableSesEvent $event): void
    {
        \assert($event instanceof SesEngagementEvent);

        $objectId = $event->campaignUuid->toRfc4122();

        $row = SesEngagementType::OPEN === $event->type
            ? $this->appHitWriter->buildOpenRow($target->adherentId, $objectId, $event->occurredAt)
            : $this->appHitWriter->buildClickRow($target->adherentId, $objectId, (string) $event->url, $event->occurredAt);

        $this->appHitWriter->insertHits([$row]);
    }
}

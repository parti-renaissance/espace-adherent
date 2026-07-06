<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Processor;

use App\AdherentMessage\Stats\EmailAppHitWriter;
use App\Ses\Webhook\AttributableSesEvent;
use App\Ses\Webhook\OpenReliability;
use App\Ses\Webhook\SesEngagementEvent;
use App\Ses\Webhook\SesEngagementParser;
use App\Ses\Webhook\SesEngagementType;
use App\Ses\Webhook\SesEventTarget;
use App\Ses\Webhook\SesEventTargetResolver;
use App\Ses\Webhook\SesEventType;
use App\Ses\Webhook\SesOpenReliabilityClassifier;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('app.ses_event_processor')]
class EngagementProcessor extends AbstractAttributableSesEventProcessor
{
    public function __construct(
        SesEngagementParser $parser,
        SesEventTargetResolver $resolver,
        private readonly EmailAppHitWriter $appHitWriter,
        private readonly SesOpenReliabilityClassifier $classifier,
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

        if (SesEngagementType::OPEN === $event->type) {
            $this->appHitWriter->insertHits([$this->buildOpenHit($target, $objectId, $event)]);

            return;
        }

        $this->appHitWriter->insertHits([
            $this->appHitWriter->buildClickRow($target->adherentId, $objectId, (string) $event->url, $event->occurredAt),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOpenHit(SesEventTarget $target, string $objectId, SesEngagementEvent $event): array
    {
        $reliability = $this->classifier->classify($event->ipAddress);

        return $this->appHitWriter->buildOpenRow(
            $target->adherentId,
            $objectId,
            $event->occurredAt,
            OpenReliability::Unreliable === $reliability,
            $this->buildProvenance($reliability, $event->ipAddress, $event->userAgent),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildProvenance(OpenReliability $reliability, ?string $ipAddress, ?string $userAgent): array
    {
        return [
            'reliability' => $reliability->value,
            'detector' => 'v1',
            'matched' => OpenReliability::Unreliable === $reliability ? ['ip_egress'] : [],
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ];
    }
}

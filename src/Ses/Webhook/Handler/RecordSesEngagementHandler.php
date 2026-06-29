<?php

declare(strict_types=1);

namespace App\Ses\Webhook\Handler;

use App\AdherentMessage\Stats\EmailAppHitWriter;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Repository\AdherentMessageRepository;
use App\Repository\AdherentRepository;
use App\Ses\Webhook\Command\RecordSesEngagementCommand;
use App\Ses\Webhook\SesEngagementParser;
use App\Ses\Webhook\SesEngagementType;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RecordSesEngagementHandler
{
    public function __construct(
        private readonly SesEngagementParser $parser,
        private readonly AdherentRepository $adherentRepository,
        private readonly AdherentMessageRepository $adherentMessageRepository,
        private readonly EmailAppHitWriter $appHitWriter,
    ) {
    }

    public function __invoke(RecordSesEngagementCommand $command): void
    {
        $event = $this->parser->parse($command->payload);
        if (null === $event) {
            return;
        }

        $adherentIds = $this->adherentRepository->findIdByUuids([$event->adherentUuid->toRfc4122()]);
        if ([] === $adherentIds) {
            return;
        }

        $message = $this->adherentMessageRepository->findOneByUuid($event->campaignUuid);
        if (!$message instanceof AdherentMessage || !$message->isSent()) {
            return;
        }

        $adherentId = (int) $adherentIds[0];
        $objectId = $event->campaignUuid->toRfc4122();

        $row = SesEngagementType::OPEN === $event->type
            ? $this->appHitWriter->buildOpenRow($adherentId, $objectId, $event->occurredAt)
            : $this->appHitWriter->buildClickRow($adherentId, $objectId, (string) $event->url, $event->occurredAt);

        $this->appHitWriter->insertHits([$row]);
    }
}

<?php

declare(strict_types=1);

namespace App\Ses\Webhook;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Repository\AdherentMessageRepository;
use App\Repository\AdherentRepository;
use Symfony\Component\Uid\Uuid;

class SesEventTargetResolver
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly AdherentMessageRepository $adherentMessageRepository,
    ) {
    }

    public function resolve(Uuid $campaignUuid, Uuid $adherentUuid): ?SesEventTarget
    {
        $adherentIds = $this->adherentRepository->findIdByUuids([$adherentUuid->toRfc4122()]);
        if ([] === $adherentIds) {
            return null;
        }

        $message = $this->adherentMessageRepository->findOneByUuid($campaignUuid);
        if (!$message instanceof AdherentMessage || !$message->isSent()) {
            return null;
        }

        return new SesEventTarget((int) $message->getId(), (int) $adherentIds[0]);
    }
}

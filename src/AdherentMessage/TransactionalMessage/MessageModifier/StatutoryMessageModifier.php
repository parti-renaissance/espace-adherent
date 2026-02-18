<?php

declare(strict_types=1);

namespace App\AdherentMessage\TransactionalMessage\MessageModifier;

use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Repository\AdherentRepository;

class StatutoryMessageModifier implements MessageModifierInterface
{
    public function __construct(private readonly AdherentRepository $adherentRepository)
    {
    }

    public function support(AdherentMessageInterface $message): bool
    {
        return $message->isStatutory();
    }

    public function modify(AdherentMessageInterface $message): void
    {
        /** @var AdherentMessageFilter $filter */
        $filter = $message->getFilter();

        $message->setRecipientCount($this->adherentRepository->countInZones(
            $filter->getZones()->toArray(),
            true,
            false
        ));
    }
}

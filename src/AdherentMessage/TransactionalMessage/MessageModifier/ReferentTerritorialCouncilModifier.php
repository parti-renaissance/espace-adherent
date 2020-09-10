<?php

namespace App\AdherentMessage\TransactionalMessage\MessageModifier;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\ReferentTerritorialCouncilFilter;
use App\Entity\AdherentMessage\ReferentTerritorialCouncilMessage;
use App\Repository\TerritorialCouncil\TerritorialCouncilMembershipRepository;

class ReferentTerritorialCouncilModifier implements MessageModifierInterface
{
    private $membershipRepository;

    public function __construct(TerritorialCouncilMembershipRepository $membershipRepository)
    {
        $this->membershipRepository = $membershipRepository;
    }

    public function support(AdherentMessageInterface $message): bool
    {
        return $message instanceof ReferentTerritorialCouncilMessage;
    }

    public function modify(AdherentMessageInterface $message): void
    {
        /** @var ReferentTerritorialCouncilFilter $filter */
        $filter = $message->getFilter();

        $message->setRecipientCount($this->membershipRepository->countForReferentTags([$filter->getReferentTag()]));
    }
}

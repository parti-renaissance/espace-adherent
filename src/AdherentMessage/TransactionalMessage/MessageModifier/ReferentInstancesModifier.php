<?php

namespace App\AdherentMessage\TransactionalMessage\MessageModifier;

use App\Entity\AdherentMessage\AdherentMessageInterface;
use App\Entity\AdherentMessage\Filter\ReferentInstancesFilter;
use App\Entity\AdherentMessage\ReferentInstancesMessage;
use App\Repository\TerritorialCouncil\PoliticalCommitteeMembershipRepository;
use App\Repository\TerritorialCouncil\TerritorialCouncilMembershipRepository;

class ReferentInstancesModifier implements MessageModifierInterface
{
    private $coTerrMembershipRepository;
    private $copolMembershipRepository;

    public function __construct(
        TerritorialCouncilMembershipRepository $membershipRepository,
        PoliticalCommitteeMembershipRepository $copolMembershipRepository
    ) {
        $this->coTerrMembershipRepository = $membershipRepository;
        $this->copolMembershipRepository = $copolMembershipRepository;
    }

    public function support(AdherentMessageInterface $message): bool
    {
        return $message instanceof ReferentInstancesMessage;
    }

    public function modify(AdherentMessageInterface $message): void
    {
        /** @var ReferentInstancesFilter $filter */
        $filter = $message->getFilter();

        $message->setRecipientCount($this->getRecipientCount($filter));
    }

    private function getRecipientCount(ReferentInstancesFilter $filter): ?int
    {
        if ($filter->getTerritorialCouncil()) {
            return $this->coTerrMembershipRepository->countForTerritorialCouncil($filter->getTerritorialCouncil(), $filter->getQualities());
        }

        if ($filter->getPoliticalCommittee()) {
            return $this->copolMembershipRepository->countForPoliticalCommittee($filter->getPoliticalCommittee(), $filter->getQualities());
        }

        return null;
    }
}

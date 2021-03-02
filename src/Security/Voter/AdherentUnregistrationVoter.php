<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Repository\AdherentMandate\AdherentMandateRepository;

class AdherentUnregistrationVoter extends AbstractAdherentVoter
{
    public const PERMISSION_UNREGISTER = 'UNREGISTER';

    /** @var AdherentMandateRepository */
    private $adherentMandateRepository;

    public function __construct(AdherentMandateRepository $adherentMandateRepository)
    {
        $this->adherentMandateRepository = $adherentMandateRepository;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION_UNREGISTER === $attribute && null === $subject;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($adherent->isUser()) {
            return true;
        }

        if ($adherent->getMemberships()->getCommitteeCandidacyMembership(true)) {
            return false;
        }

        if (($coTerrMembership = $adherent->getTerritorialCouncilMembership()) && $coTerrMembership->getActiveCandidacy()) {
            return false;
        }

        if ($this->adherentMandateRepository->hasActiveMandate($adherent)) {
            return false;
        }

        return $adherent->isBasicAdherent();
    }
}

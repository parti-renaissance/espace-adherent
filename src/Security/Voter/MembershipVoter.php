<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Repository\AdherentMandate\AdherentMandateRepository;

class MembershipVoter extends AbstractAdherentVoter
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
        if ($adherent->isBasicAdherent() && !$this->adherentMandateRepository->hasActiveMandate($adherent)) {
            return true;
        }

        if ($adherent->isUser()) {
            return true;
        }

        return false;
    }
}

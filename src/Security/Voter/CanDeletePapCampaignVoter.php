<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Pap\Campaign;
use App\Scope\ScopeGeneratorResolver;

class CanDeletePapCampaignVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_DELETE_PAP_CAMPAIGN';

    private ScopeGeneratorResolver $scopeGeneratorResolver;

    public function __construct(ScopeGeneratorResolver $scopeGeneratorResolver)
    {
        $this->scopeGeneratorResolver = $scopeGeneratorResolver;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($subject->isNationalVisibility()) {
            return false;
        }

        if (($scope = $this->scopeGeneratorResolver->generate()) && $scope->getDelegatedAccess()) {
            $adherent = $scope->getDelegator();
        }

        /** @var Campaign $subject */
        if ($adherent !== $subject->getCreatedByAdherent()) {
            return false;
        }

        return $subject->getBeginAt() > new \DateTime();
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof Campaign;
    }
}

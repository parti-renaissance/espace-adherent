<?php

namespace App\Security\Voter;

use App\Address\Address;
use App\Entity\Adherent;
use App\Repository\ReferentTagRepository;

class ManagedUserVoter extends AbstractAdherentVoter
{
    public const IS_MANAGED_USER = 'IS_MANAGED_USER';

    protected function supports($attribute, $subject)
    {
        return self::IS_MANAGED_USER === $attribute && $subject instanceof Adherent;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $user, $adherent): bool
    {
        $isGranted = false;

        // Check Referent role
        /** @var Adherent $adherent */
        if ($user->isReferent() || $user->isCoReferent()) {
            $isGranted = (bool) array_intersect(
                $adherent->getReferentTagCodes(),
                $user->isReferent() ? $user->getManagedAreaTagCodes() : $user->getReferentOfReferentTeam()->getManagedAreaTagCodes()
            );
        }

        // Check Deputy role
        if (!$isGranted && $user->isDeputy()) {
            $isGranted = (bool) array_intersect(
                $adherent->getReferentTagCodes(),
                [$user->getManagedDistrict()->getReferentTag()->getCode()]
            );
        }

        // Check Senator role
        if (!$isGranted && $user->isSenator()) {
            $code = $user->getSenatorArea()->getDepartmentTag()->getCode();
            $isGranted = (bool) array_intersect($adherent->getReferentTagCodes(), [$code])
                || (ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG === $code && Address::FRANCE !== $adherent->getCountry());
        }

        return $isGranted;
    }
}

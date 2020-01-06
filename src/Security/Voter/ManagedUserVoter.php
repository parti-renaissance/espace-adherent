<?php

namespace AppBundle\Security\Voter;

use AppBundle\Entity\Adherent;

class ManagedUserVoter extends AbstractAdherentVoter
{
    public const IS_MANAGED_USER = 'IS_MANAGED_USER';

    protected function supports($attribute, $subject)
    {
        return self::IS_MANAGED_USER === $attribute && $subject instanceof Adherent;
    }

    /**
     * @var Adherent
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $user, $adherent): bool
    {
        $isGranted = false;

        // Check Referent role
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
            $isGranted = (bool) array_intersect(
                $adherent->getReferentTagCodes(),
                [$user->getSenatorArea()->getDepartmentTag()->getCode()]
            );
        }

        return $isGranted;
    }
}

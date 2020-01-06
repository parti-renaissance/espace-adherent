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
        if ($user->isReferent() || $user->isCoReferent()) {
            return (bool) array_intersect(
                $adherent->getReferentTagCodes(),
                $user->isReferent() ? $user->getManagedAreaTagCodes() : $user->getReferentOfReferentTeam()->getManagedAreaTagCodes()
            );
        }

        if ($user->isDeputy()) {
            return (bool) array_intersect(
                $adherent->getReferentTagCodes(),
                [$user->getManagedDistrict()->getReferentTag()->getCode()]
            );
        }

        if ($user->isSenator()) {
            return (bool) array_intersect(
                $adherent->getReferentTagCodes(),
                [$user->getSenatorArea()->getDepartmentTag()->getCode()]
            );
        }

        return false;
    }
}

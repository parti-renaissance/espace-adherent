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
        if ($user->isReferent() || $user->isDelegatedReferent()) {
            $isGranted = (bool) array_intersect(
                $adherent->getReferentTagCodes(),
                $user->getAllReferentManagedTagsCodes(),
            );
        }

        // Check Deputy role
        if (!$isGranted && ($user->isDeputy() || $user->isDelegatedDeputy())) {
            $isGranted = (bool) array_intersect(
                $adherent->getReferentTagCodes(),
                $user->getAllDeputyManagedTagsCodes(),
            );
        }

        // Check Senator role
        if (!$isGranted && ($user->isSenator() || $user->isDelegatedSenator())) {
            $codes = $user->getAllSenatorManagedTagsCodes();
            $isGranted = (bool) array_intersect($adherent->getReferentTagCodes(), $codes)
                || (\in_array(ReferentTagRepository::FRENCH_OUTSIDE_FRANCE_TAG, $codes, true) && Address::FRANCE !== $adherent->getCountry());
        }

        return $isGranted;
    }
}

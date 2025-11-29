<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Committee\CommitteePermissionEnum;
use App\Entity\Adherent;
use App\Entity\Committee;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CommitteeShowVoter extends AbstractAdherentVoter
{
    protected function supports(string $attribute, $group): bool
    {
        return CommitteePermissionEnum::SHOW === $attribute && $group instanceof Committee;
    }

    /**
     * @param string    $attribute
     * @param Committee $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if ($subject->isApproved()) {
            return true;
        }

        return parent::voteOnAttribute($attribute, $subject, $token);
    }

    /**
     * @param Committee $subject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return $subject->isCreatedBy($adherent->getUuid());
    }
}

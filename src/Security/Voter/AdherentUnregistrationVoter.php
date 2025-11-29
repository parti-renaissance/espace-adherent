<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Adherent;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdherentUnregistrationVoter extends Voter
{
    public const PERMISSION_UNREGISTER = 'UNREGISTER';

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION_UNREGISTER === $attribute && $subject instanceof Adherent;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var Adherent $subject */
        if ($subject->isToDelete()) {
            return false;
        }

        return !$subject->isPresidentDepartmentalAssembly()
            && !$subject->isAnimator()
            && !$subject->isDeputy()
            && !$subject->isRegionalDelegate()
            && !$subject->getCommitteeMembership()?->hasActiveCommitteeCandidacy(true);
    }
}

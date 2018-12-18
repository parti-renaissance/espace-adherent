<?php

namespace AppBundle\Security\Voter;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\AuthoredInterface;

class AuthoredVoter extends AbstractAdherentVoter
{
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        /** @var AuthoredInterface $subject */
        return $subject->getAuthor()->equals($adherent);
    }

    protected function supports($attribute, $subject)
    {
        return 'IS_AUTHOR_OF' === $attribute && $subject instanceof AuthoredInterface;
    }
}

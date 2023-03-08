<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\AuthoredInterface;
use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthorVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'IS_AUTHOR_OF';

    public function __construct(private readonly SessionInterface $session)
    {
    }

    /**
     * @param AuthoredInterface $subject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if (!$subject->getAuthor()) {
            return false;
        }

        if ($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->session->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            return $subject->getAuthor()->equals($delegatedAccess->getDelegator());
        }

        return $subject->getAuthor()->equals($adherent);
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof AuthoredInterface;
    }
}

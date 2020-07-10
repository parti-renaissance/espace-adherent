<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\AuthoredInterface;
use App\Entity\MyTeam\DelegatedAccess;
use Symfony\Component\HttpFoundation\RequestStack;

class AuthorVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'IS_AUTHOR_OF';

    /** @var RequestStack */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($delegatedAccess = $adherent->getReceivedDelegatedAccessByUuid($this->requestStack->getMasterRequest()->attributes->get(DelegatedAccess::ATTRIBUTE_KEY))) {
            return $subject->getAuthor()->equals($delegatedAccess->getDelegator());
        }

        /** @var AuthoredInterface $subject */
        if ($subject->getAuthor()->equals($adherent)) {
            return true;
        }

        foreach ($adherent->getReceivedDelegatedAccesses() as $delegatedAccess) {
            if ($subject->getAuthor()->equals($delegatedAccess->getDelegator())) {
                return true;
            }
        }

        return false;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof AuthoredInterface;
    }
}

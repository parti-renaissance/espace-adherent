<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\AuthoredInterface;
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
        if ($delegatedAccess = $this->requestStack->getMasterRequest()->attributes->get('delegatedAccess')) {
            return $subject->getAuthor()->equals($delegatedAccess->getDelegator());
        }

        /** @var AuthoredInterface $subject */
        return $subject->getAuthor()->equals($adherent);
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof AuthoredInterface;
    }
}

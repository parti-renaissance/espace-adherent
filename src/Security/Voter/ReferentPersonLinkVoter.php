<?php

namespace App\Security\Voter;

use App\Repository\ReferentRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ReferentPersonLinkVoter extends Voter
{
    public const IS_ROOT_REFERENT = 'IS_ROOT_REFERENT';
    private $referentRepository;

    public function __construct(ReferentRepository $referentRepository)
    {
        $this->referentRepository = $referentRepository;
    }

    protected function supports($attribute, $subject)
    {
        return static::IS_ROOT_REFERENT === $attribute || (
            \is_array($attribute) && \in_array(static::IS_ROOT_REFERENT, $attribute, true)
        );
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return (bool) $this->referentRepository->findOneByEmail($token->getUser()->getEmailAddress());
    }
}

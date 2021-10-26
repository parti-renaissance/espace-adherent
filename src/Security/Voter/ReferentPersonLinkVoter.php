<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Repository\ReferentRepository;

class ReferentPersonLinkVoter extends AbstractAdherentVoter
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

    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        return $this->referentRepository->exists($adherent->getEmailAddress());
    }
}

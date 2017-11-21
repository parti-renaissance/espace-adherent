<?php

namespace AppBundle\CitizenProject\Voter;

use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\CitizenProject\CitizenProjectPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdministrateCitizenProjectVoter extends Voter
{
    private $manager;

    public function __construct(CitizenProjectManager $manager)
    {
        $this->manager = $manager;
    }

    protected function supports($attribute, $subject): bool
    {
        return CitizenProjectPermissions::ADMINISTRATE === $attribute && $subject instanceof CitizenProject;
    }

    /**
     * @param string         $attribute
     * @param CitizenProject $citizenProject
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $citizenProject, TokenInterface $token): bool
    {
        if ($token instanceof AnonymousToken) {
            return false;
        }

        $administrator = $token->getUser();

        if (!$administrator instanceof Adherent) {
            return false;
        }

        return $this->manager->administrateCitizenProject($administrator, $citizenProject);
    }
}

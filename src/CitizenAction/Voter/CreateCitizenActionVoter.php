<?php

namespace AppBundle\CitizenAction\Voter;

use AppBundle\CitizenAction\CitizenActionPermissions;
use AppBundle\CitizenProject\CitizenProjectManager;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenProject;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CreateCitizenActionVoter extends Voter
{
    private $manager;

    public function __construct(CitizenProjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof CitizenProject && CitizenActionPermissions::CREATE === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return
            $token->getUser() instanceof Adherent
            && $subject instanceof CitizenProject
            && $subject->isApproved()
            && $this->manager->administrateCitizenProject($token->getUser(), $subject);
    }
}

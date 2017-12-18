<?php

namespace AppBundle\CitizenAction\Voter;

use AppBundle\CitizenAction\CitizenActionPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenAction;
use AppBundle\Event\EventRegistrationManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RegisterCitizenActionVoter extends Voter
{
    private $eventRegistrationManager;

    public function __construct(EventRegistrationManager $eventRegistrationManager)
    {
        $this->eventRegistrationManager = $eventRegistrationManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function supports($attribute, $subject)
    {
        return $subject instanceof CitizenAction && CitizenActionPermissions::REGISTER === $attribute;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return
            $token->getUser() instanceof Adherent
            && !$this->eventRegistrationManager->findByAdherentEmailAndEvent($token->getUser()->getEmailAddress(), $subject);
    }
}

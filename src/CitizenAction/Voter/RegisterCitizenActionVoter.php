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
        $attributes = [
            CitizenActionPermissions::REGISTER,
            CitizenActionPermissions::UNREGISTER,
        ];

        return in_array(strtoupper($attribute), $attributes, true) && $subject instanceof CitizenAction;
    }

    /**
     * {@inheritdoc}
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $adherent = $token->getUser();
        if (!$adherent instanceof Adherent) {
            return false;
        }

        $registration = $this->eventRegistrationManager->findByAdherentEmailAndEvent($token->getUser()->getEmailAddress(), $subject);
        if (CitizenActionPermissions::REGISTER === $attribute) {
            return !$registration;
        }

        return (bool) $registration;
    }
}

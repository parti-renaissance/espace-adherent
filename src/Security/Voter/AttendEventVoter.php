<?php

namespace AppBundle\Security\Voter;

use AppBundle\CitizenAction\CitizenActionPermissions;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\BaseEvent;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\Event;
use AppBundle\Event\EventPermissions;
use AppBundle\Repository\EventRegistrationRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AttendEventVoter extends AbstractAdherentVoter
{
    private $registrationRepository;

    public function __construct(EventRegistrationRepository $registrationRepository)
    {
        $this->registrationRepository = $registrationRepository;
    }

    protected function supports($attribute, $subject)
    {
        return $subject instanceof Event && \in_array($attribute, EventPermissions::ATTEND, true)
            || $subject instanceof CitizenAction && \in_array($attribute, CitizenActionPermissions::ATTEND, true)
        ;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if ((EventPermissions::REGISTER === $attribute || CitizenActionPermissions::REGISTER === $attribute)
            && !$token->getUser()
        ) {
            // Anonymous are always granted to register, because we cannot check if they already did
            return true;
        }

        return parent::voteOnAttribute($attribute, $subject, $token);
    }

    /**
     * @param BaseEvent $subject
     */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        $isRegistered = (bool) $this->registrationRepository->findByRegisteredEmailAndEvent(
            $adherent->getEmailAddress(),
            $subject
        );

        if (EventPermissions::UNREGISTER === $attribute || CitizenActionPermissions::UNREGISTER === $attribute) {
            return $isRegistered;
        }

        return !$isRegistered;
    }
}

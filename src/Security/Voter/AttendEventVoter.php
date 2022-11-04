<?php

namespace App\Security\Voter;

use App\Entity\Adherent;
use App\Entity\Event\BaseEvent;
use App\Entity\Event\CommitteeEvent;
use App\Event\EventPermissions;
use App\Repository\EventRegistrationRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AttendEventVoter extends AbstractAdherentVoter
{
    private $registrationRepository;

    public function __construct(EventRegistrationRepository $registrationRepository)
    {
        $this->registrationRepository = $registrationRepository;
    }

    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof CommitteeEvent && \in_array($attribute, EventPermissions::ATTEND, true);
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if (EventPermissions::REGISTER === $attribute && !$token->getUser()) {
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
        $isRegistered = $this->registrationRepository->isAlreadyRegistered($adherent->getEmailAddress(), $subject);

        if (EventPermissions::UNREGISTER === $attribute) {
            return $isRegistered;
        }

        return !$isRegistered;
    }
}

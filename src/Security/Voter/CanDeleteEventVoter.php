<?php

namespace App\Security\Voter;

use App\Entity\Event\BaseEvent;
use App\Repository\EventRegistrationRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CanDeleteEventVoter extends Voter
{
    public const PERMISSION = 'CAN_DELETE_EVENT';

    private EventRegistrationRepository $eventRegistrationRepository;

    public function __construct(EventRegistrationRepository $eventRegistrationRepository)
    {
        $this->eventRegistrationRepository = $eventRegistrationRepository;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        return $this->eventRegistrationRepository->countEventParticipantsWithoutCreator($subject) < 1;
    }

    protected function supports($attribute, $subject)
    {
        return self::PERMISSION === $attribute && $subject instanceof BaseEvent;
    }
}

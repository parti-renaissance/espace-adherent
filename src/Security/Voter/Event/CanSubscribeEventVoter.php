<?php

declare(strict_types=1);

namespace App\Security\Voter\Event;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Repository\EventRegistrationRepository;
use App\Security\Voter\AbstractAdherentVoter;

class CanSubscribeEventVoter extends AbstractAdherentVoter
{
    public const PERMISSION = 'CAN_SUBSCRIBE_EVENT';

    public function __construct(private readonly EventRegistrationRepository $eventRegistrationRepository)
    {
    }

    /** @param Event $subject */
    protected function doVoteOnAttribute(string $attribute, Adherent $adherent, $subject): bool
    {
        if ($subject->isInvitation()) {
            return null !== $this->eventRegistrationRepository->findInvitationByEventAndAdherent($subject, $adherent);
        }

        return true;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof Event;
    }
}

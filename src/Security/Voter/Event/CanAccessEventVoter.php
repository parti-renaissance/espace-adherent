<?php

declare(strict_types=1);

namespace App\Security\Voter\Event;

use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Repository\EventRegistrationRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CanAccessEventVoter extends Voter
{
    public const PERMISSION = 'CAN_ACCESS_EVENT';

    public function __construct(private readonly EventRegistrationRepository $eventRegistrationRepository)
    {
    }

    /** @param Event $subject */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if ($subject->isPublic() || $subject->isPrivate()) {
            return true;
        }

        if (!($adherent = $token->getUser()) instanceof Adherent) {
            return false;
        }

        if ($subject->isForAdherent()) {
            return true;
        }

        if ($subject->isInvitation()) {
            return null !== $this->eventRegistrationRepository->findAdherentRegistration($subject->getUuidAsString(), $adherent->getUuidAsString(), null);
        }

        return false;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof Event;
    }
}

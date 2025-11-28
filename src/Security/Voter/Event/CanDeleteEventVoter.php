<?php

declare(strict_types=1);

namespace App\Security\Voter\Event;

use App\Entity\Event\Event;
use App\Repository\EventRegistrationRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CanDeleteEventVoter extends Voter
{
    public const PERMISSION = 'CAN_DELETE_EVENT';

    public function __construct(private readonly EventRegistrationRepository $eventRegistrationRepository)
    {
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        return $this->eventRegistrationRepository->countEventParticipantsWithoutCreator($subject) < 1;
    }

    protected function supports(string $attribute, $subject): bool
    {
        return self::PERMISSION === $attribute && $subject instanceof Event;
    }
}

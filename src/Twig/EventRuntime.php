<?php

namespace AppBundle\Twig;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Repository\EventRegistrationRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class EventRuntime
{
    private $eventRegistrationRepository;

    public function __construct(EventRegistrationRepository $eventRegistrationRepository)
    {
        $this->eventRegistrationRepository = $eventRegistrationRepository;
    }

    public function isEventAlreadyParticipating(Event $event, ?UserInterface $user): bool
    {
        if (!$user instanceof Adherent) {
            return false;
        }

        return (bool) $this->eventRegistrationRepository->findGuestRegistration($event->getUuid(), $user->getEmailAddress());
    }
}

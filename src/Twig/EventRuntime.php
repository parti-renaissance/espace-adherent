<?php

namespace App\Twig;

use App\Address\AddressInterface;
use App\Entity\Adherent;
use App\Entity\Event\Event;
use App\Repository\EventRegistrationRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\RuntimeExtensionInterface;

class EventRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly EventRegistrationRepository $eventRegistrationRepository)
    {
    }

    public function isEventAlreadyParticipating(Event $event, ?UserInterface $user): bool
    {
        if (!$user instanceof Adherent) {
            return false;
        }

        return $this->eventRegistrationRepository->isAlreadyRegistered($user->getEmailAddress(), $event);
    }

    public function offsetTimeZone(string $timeZone = AddressInterface::DEFAULT_TIME_ZONE): string
    {
        if (AddressInterface::DEFAULT_TIME_ZONE !== $timeZone) {
            $datetime = new \DateTime('now');
            $tz = new \DateTimeZone($timeZone);
            $datetime->setTimezone($tz);

            return ' UTC '.$datetime->format('P');
        }

        return '';
    }
}

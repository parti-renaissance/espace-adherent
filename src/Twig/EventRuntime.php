<?php

namespace App\Twig;

use App\Address\GeoCoder;
use App\Entity\Adherent;
use App\Entity\Event;
use App\Repository\EventRegistrationRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\RuntimeExtensionInterface;

class EventRuntime implements RuntimeExtensionInterface
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

        return $this->eventRegistrationRepository->isAlreadyRegistered($user->getEmailAddress(), $event);
    }

    public function offsetTimeZone(string $timeZone = GeoCoder::DEFAULT_TIME_ZONE): string
    {
        if (GeoCoder::DEFAULT_TIME_ZONE !== $timeZone) {
            $datetime = new \DateTime('now');
            $tz = new \DateTimeZone($timeZone);
            $datetime->setTimezone($tz);

            return ' UTC '.$datetime->format('P');
        }

        return '';
    }
}

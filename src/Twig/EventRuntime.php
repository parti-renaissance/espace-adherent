<?php

namespace AppBundle\Twig;

use AppBundle\Address\GeoCoder;
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

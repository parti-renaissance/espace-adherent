<?php

namespace AppBundle\CitizenAction;

use AppBundle\Collection\EventRegistrationCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\EventRegistration;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CitizenActionRepository;
use AppBundle\Repository\EventRegistrationRepository;

class CitizenActionManager
{
    private $citizenActionRepository;
    private $eventRegistrationRepository;
    private $adherentRepository;

    public function __construct(
        CitizenActionRepository $citizenActionRepository,
        EventRegistrationRepository $eventRegistrationRepository,
        AdherentRepository $adherentRepository
    ) {
        $this->citizenActionRepository = $citizenActionRepository;
        $this->eventRegistrationRepository = $eventRegistrationRepository;
        $this->adherentRepository = $adherentRepository;
    }

    public function removeOrganizerCitizenActions(Adherent $adherent): void
    {
        $this->citizenActionRepository->removeOrganizerEvents($adherent, CitizenActionRepository::TYPE_PAST, true);
        $this->citizenActionRepository->removeOrganizerEvents($adherent, CitizenActionRepository::TYPE_UPCOMING);
    }

    public function getRegistrations(CitizenAction $citizenAction): EventRegistrationCollection
    {
        return $this->eventRegistrationRepository->findByEvent($citizenAction);
    }

    public function populateRegistrationWithAdherentsInformations(EventRegistrationCollection $registrations): array
    {
        $adherentsEmails = [];
        $eventsRegistrationHydrated = [];

        /** @var EventRegistration $registration */
        foreach ($registrations as $registration) {
            if (null === $registration->getEmailAddress()) {
                continue;
            }

            $adherentsEmails[] = $registration->getEmailAddress();
        }

        $adherents = $this->adherentRepository->findByEmails($adherentsEmails);

        foreach ($registrations as $registration) {
            $find = false;
            $adherentFind = null;
            /** @var Adherent $adherent */
            foreach ($adherents as $adherent) {
                if ($adherent->getEmailAddress() !== $registration->getEmailAddress()) {
                    continue;
                }

                $find = true;
                $adherentFind = $adherent;
                break;
            }

            if (!$find) {
                $eventsRegistrationHydrated[] = [
                    'age' => '',
                    'lastNameInitial' => '',
                    'lastName' => '',
                    'cityName' => '',
                ];

                continue;
            }

            $eventsRegistrationHydrated[] = [
                'age' => $adherentFind->getAge(),
                'lastNameInitial' => $adherentFind->getLastNameInitial(),
                'lastName' => $adherentFind->getLastName(),
                'firstName' => $adherentFind->getFirstName(),
                'postalCode' => $adherentFind->getPostalCode(),
                'cityName' => $adherentFind->getCityName(),
                'createdAt' => $registration->getCreatedAt(),
            ];
        }

        return $eventsRegistrationHydrated;
    }
}

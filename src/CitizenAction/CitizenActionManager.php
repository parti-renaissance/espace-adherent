<?php

namespace AppBundle\CitizenAction;

use AppBundle\Collection\EventRegistrationCollection;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\CitizenAction;
use AppBundle\Entity\EventRegistration;
use AppBundle\Event\EventRegistrationManager;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CitizenActionRepository;
use AppBundle\Repository\EventRegistrationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;

class CitizenActionManager
{
    private $citizenActionRepository;
    private $eventRegistrationRepository;
    private $adherentRepository;
    private $eventRegistrationManager;

    public function __construct(
        CitizenActionRepository $citizenActionRepository,
        EventRegistrationRepository $eventRegistrationRepository,
        AdherentRepository $adherentRepository,
        EventRegistrationManager $eventRegistrationManager
    ) {
        $this->citizenActionRepository = $citizenActionRepository;
        $this->eventRegistrationRepository = $eventRegistrationRepository;
        $this->adherentRepository = $adherentRepository;
        $this->eventRegistrationManager = $eventRegistrationManager;
    }

    public function getRegistrations(CitizenAction $citizenAction): EventRegistrationCollection
    {
        return $this->eventRegistrationRepository->findByEvent($citizenAction);
    }

    public function populateRegistrationWithAdherentsInformations(
        EventRegistrationCollection $registrations,
        ArrayCollection $citizenProjectAdministrators
    ): array {
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
            $adherentFind = null;
            /** @var Adherent $adherent */
            foreach ($adherents as $adherent) {
                if ($adherent->getEmailAddress() !== $registration->getEmailAddress()) {
                    continue;
                }
                $adherentFind = $adherent;

                break;
            }

            if (!$adherentFind instanceof Adherent) {
                $eventsRegistrationHydrated[] = [
                    'uuid' => $registration->getUuid(),
                    'age' => '',
                    'lastNameInitial' => $registration->getLastNameInitial(),
                    'lastName' => $registration->getLastName(),
                    'firstName' => $registration->getFirstName(),
                    'postalCode' => $registration->getPostalCode(),
                    'cityName' => '',
                    'createdAt' => $registration->getCreatedAt(),
                    'administrator' => false,
                ];

                continue;
            }

            $eventsRegistrationHydrated[] = [
                'uuid' => $registration->getUuid(),
                'age' => $adherentFind->getAge(),
                'lastNameInitial' => $adherentFind->getLastNameInitial(),
                'lastName' => $adherentFind->getLastName(),
                'firstName' => $adherentFind->getFirstName(),
                'postalCode' => $adherentFind->getPostalCode(),
                'cityName' => $adherentFind->getCityName(),
                'createdAt' => $registration->getCreatedAt(),
                'administrator' => $citizenProjectAdministrators->contains($adherent),
            ];
        }

        usort($eventsRegistrationHydrated, function ($a, $b) {
            return $b['administrator'] <=> $a['administrator'];
        });

        return $eventsRegistrationHydrated;
    }

    public function unregisterFromCitizenAction(CitizenAction $citizenAction, Adherent $adherent): void
    {
        if (!$registration = $this->eventRegistrationManager->searchAdherentRegistration($citizenAction, $adherent)) {
            throw new EntityNotFoundException(sprintf('Unable to find event registration by CitizenAction UUID (%s) and adherent UUID (%s)', $citizenAction->getUuid()->toString(), $adherent->getUuid()->toString()));
        }

        $this->eventRegistrationManager->remove($registration);
    }
}

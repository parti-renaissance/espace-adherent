<?php

namespace AppBundle\Donation;

use AppBundle\Repository\AdherentRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DonatorSubscriber implements EventSubscriberInterface
{
    private $adherentRepository;

    public function __construct(AdherentRepository $adherentRepository)
    {
        $this->adherentRepository = $adherentRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            DonationEvents::CREATED => ['attachAdherent'],
            DonationEvents::DONATOR_UPDATED => ['checkLastSuccessfulDonation'],
        ];
    }

    /**
     * Match and attach an adherent with a donator
     */
    public function attachAdherent(DonationWasCreatedEvent $event): void
    {
        $donator = $event->getDonation()->getDonator();

        if (!$donator->isAdherent()) {
            $donator->setAdherent($this->adherentRepository->findOneForMatching(
                $donator->getEmailAddress(),
                $donator->getFirstName(),
                $donator->getLastName()
            ));
        }
    }

    public function checkLastSuccessfulDonation(DonatorWasUpdatedEvent $event): void
    {
        $donator = $event->getDonator();
        $donator->setLastSuccessfulDonation(null);

        foreach ($donator->getDonations() as $donation) {
            if (!$lastSuccessDate = $donation->getLastSuccessDate()) {
                continue;
            }

            $lastSuccessfulDonation = $donator->getLastSuccessfulDonation();

            if (
                !$lastSuccessfulDonation
                || $lastSuccessfulDonation->getLastSuccessDate() < $donation->getLastSuccessDate()
            ) {
                $donation->markAsLastSuccessfulDonation();
            }
        }
    }
}

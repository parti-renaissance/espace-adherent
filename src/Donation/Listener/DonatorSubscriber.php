<?php

namespace App\Donation\Listener;

use App\Donation\DonationEvents;
use App\Donation\Event\DonationWasCreatedEvent;
use App\Donation\Event\DonatorWasUpdatedEvent;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DonatorSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public static function getSubscribedEvents(): array
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

        if (!$donator->isAdherent() && $donator->getEmailAddress() && $donator->getFirstName() && $donator->getLastName()) {
            $donator->setAdherent($adherent = $this->adherentRepository->findOneForMatching(
                $donator->getEmailAddress(),
                $donator->getFirstName(),
                $donator->getLastName()
            ));

            $this->em->flush();

            if ($adherent) {
                $this->adherentRepository->refreshDonationDates($adherent);
            }
        }
    }

    public function checkLastSuccessfulDonation(DonatorWasUpdatedEvent $event): void
    {
        $donator = $event->getDonator();

        $donator->computeLastSuccessfulDonation();

        $this->em->flush();
    }
}

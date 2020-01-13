<?php

namespace AppBundle\Donation;

use AppBundle\Repository\AdherentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DonatorSubscriber implements EventSubscriberInterface
{
    private $adherentRepository;
    /**
     * @var ObjectManager
     */
    private $em;

    public function __construct(AdherentRepository $adherentRepository, ObjectManager $em)
    {
        $this->adherentRepository = $adherentRepository;
        $this->em = $em;
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

            $currentSuccessfulDonation = $donator->getLastSuccessfulDonation();

            if (!$currentSuccessfulDonation || $currentSuccessfulDonation->getLastSuccessDate() < $lastSuccessDate) {
                $donation->markAsLastSuccessfulDonation();
            }
        }

        $this->em->flush();
    }
}

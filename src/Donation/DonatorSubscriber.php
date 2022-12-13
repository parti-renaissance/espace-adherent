<?php

namespace App\Donation;

use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
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

        $donator->computeLastSuccessfulDonation();

        $this->em->flush();
    }
}

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
        ];
    }

    /**
     * Match and attach an adherent with a donator
     */
    public function attachAdherent(DonationWasCreatedEvent $donation): void
    {
        $donator = $donation->getDonation()->getDonator();

        if (!$donator->isAdherent()) {
            $donator->setAdherent($this->adherentRepository->findOneForMatching(
                $donator->getEmailAddress(),
                $donator->getFirstName(),
                $donator->getLastName()
            ));
        }
    }
}

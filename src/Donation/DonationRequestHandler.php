<?php

namespace AppBundle\Donation;

use AppBundle\Entity\Donation;
use AppBundle\Entity\Donator;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\DonatorRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DonationRequestHandler
{
    private $dispatcher;
    private $manager;
    private $donationFactory;
    private $donatorFactory;
    private $donatorRepository;
    private $donatorManager;
    private $adherentRepository;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        ManagerRegistry $doctrine,
        DonationFactory $donationFactory,
        DonatorFactory $donatorFactory,
        DonatorRepository $donatorRepository,
        DonatorManager $donatorManager,
        AdherentRepository $adherentRepository
    ) {
        $this->dispatcher = $dispatcher;
        $this->manager = $doctrine->getManagerForClass(Donation::class);
        $this->donationFactory = $donationFactory;
        $this->donatorFactory = $donatorFactory;
        $this->donatorRepository = $donatorRepository;
        $this->donatorManager = $donatorManager;
        $this->adherentRepository = $adherentRepository;
    }

    public function handle(DonationRequest $donationRequest): Donation
    {
        if ($donator = $this->donatorRepository->findOneForMatching(
            $donationRequest->getEmailAddress(),
            $donationRequest->getFirstName(),
            $donationRequest->getLastName()
        )) {

        } else {
            $donator = $this->createDonator($donationRequest);
        }

        $donation = $this->donationFactory->createFromDonationRequest($donationRequest, $donator);

        $donator->addDonation($donation);

        $this->dispatcher->dispatch(DonationEvents::CREATED, new DonationWasCreatedEvent($donation));

        $this->manager->persist($donator);
        $this->manager->persist($donation);

        $this->manager->flush();

        return $donation;
    }

    private function createDonator(DonationRequest $donationRequest): Donator
    {
        $donator = $this->donatorFactory->createFromDonationRequest($donationRequest);
        $donator->setIdentifier($this->donatorManager->incrementeIdentifier());

        return $donator;
    }
}

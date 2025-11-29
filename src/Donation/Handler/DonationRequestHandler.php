<?php

declare(strict_types=1);

namespace App\Donation\Handler;

use App\Donation\DonationEvents;
use App\Donation\DonationFactory;
use App\Donation\DonatorFactory;
use App\Donation\DonatorManager;
use App\Donation\Event\DonationWasCreatedEvent;
use App\Donation\Request\DonationRequest;
use App\Entity\Adherent;
use App\Entity\Donation;
use App\Entity\Donator;
use App\Repository\DonatorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DonationRequestHandler
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface $manager,
        private readonly DonationFactory $donationFactory,
        private readonly DonatorFactory $donatorFactory,
        private readonly DonatorRepository $donatorRepository,
        private readonly DonatorManager $donatorManager,
    ) {
    }

    public function handle(DonationRequest $donationRequest, ?Adherent $adherent = null, bool $forReAdhesion = false): Donation
    {
        if (!$donator = $this->donatorRepository->findOneForMatching(
            $donationRequest->getEmailAddress(),
            $donationRequest->getFirstName(),
            $donationRequest->getLastName()
        )) {
            $donator = $this->createDonator($donationRequest);
            $this->manager->persist($donator);
            $this->manager->flush();
        }

        if ($adherent) {
            $this->donatorRepository->updateDonatorLink($adherent);
            $this->manager->refresh($donator);
        }

        $donation = $this->donationFactory->createFromDonationRequest($donationRequest, $donator, $forReAdhesion);
        $donator->addDonation($donation);

        $this->manager->persist($donation);
        $this->manager->flush();

        $this->dispatcher->dispatch(new DonationWasCreatedEvent($donation), DonationEvents::CREATED);

        return $donation;
    }

    public function handleRetry(Donation $initialDonation): Donation
    {
        $donator = $initialDonation->getDonator();
        $donation = $this->donationFactory->duplicate($initialDonation);
        $donator->addDonation($donation);

        $this->dispatcher->dispatch(new DonationWasCreatedEvent($donation), DonationEvents::CREATED);

        $this->manager->persist($donation);
        $this->manager->flush();

        return $donation;
    }

    private function createDonator(DonationRequest $donationRequest): Donator
    {
        $donator = $this->donatorFactory->createFromDonationRequest($donationRequest);
        $donator->setIdentifier($this->donatorManager->incrementIdentifier());

        return $donator;
    }
}

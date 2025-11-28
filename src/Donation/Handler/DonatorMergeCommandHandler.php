<?php

declare(strict_types=1);

namespace App\Donation\Handler;

use App\Adherent\Tag\Command\RefreshAdherentTagCommand;
use App\Donation\Command\DonatorMergeCommand;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class DonatorMergeCommandHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly AdherentRepository $adherentRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function handle(DonatorMergeCommand $donatorMergeCommand): void
    {
        $sourceDonator = $donatorMergeCommand->getSourceDonator();
        $destinationDonator = $donatorMergeCommand->getDestinationDonator();

        foreach ($sourceDonator->getDonations() as $donation) {
            $sourceDonator->removeDonation($donation);
            $donation->setDonator($destinationDonator);
        }

        $this->em->remove($sourceDonator);
        $this->em->flush();

        $adherents = array_filter([
            $sourceDonator->getAdherent(),
            $destinationDonator->getAdherent(),
        ]);

        foreach ($adherents as $adherent) {
            $this->adherentRepository->refreshDonationDates($adherent);
            $this->bus->dispatch(new RefreshAdherentTagCommand($adherent->getUuid()));
        }
    }
}

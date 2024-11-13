<?php

namespace App\Donation\Handler;

use App\Adherent\Tag\Command\RefreshAdherentTagCommand;
use App\Donation\Command\DonatorMergeCommand;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class DonatorMergeCommandHandler
{
    public function __construct(private readonly EntityManagerInterface $em, private readonly MessageBusInterface $bus)
    {
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

        if ($sourceDonator->isAdherent()) {
            $this->bus->dispatch(new RefreshAdherentTagCommand($sourceDonator->getAdherent()->getUuid()));
        }

        if ($destinationDonator->isAdherent()) {
            $this->bus->dispatch(new RefreshAdherentTagCommand($destinationDonator->getAdherent()->getUuid()));
        }
    }
}

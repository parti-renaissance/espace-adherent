<?php

namespace App\Renaissance\Membership\Admin;

use App\Donation\DonationRequest;
use App\Donation\DonationRequestHandler;
use App\Donation\PayboxPaymentSubscription;
use App\Entity\Donation;
use App\Membership\AdherentFactory;
use App\Membership\MembershipSourceEnum;
use Doctrine\ORM\EntityManagerInterface;

class AdherentCreateCommandHandler
{
    public function __construct(
        private readonly AdherentFactory $adherentFactory,
        private readonly DonationRequestHandler $donationRequestHandler,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function createCommand(): AdherentCreateCommand
    {
        return new AdherentCreateCommand(MembershipSourceEnum::RENAISSANCE);
    }

    public function handle(AdherentCreateCommand $command): void
    {
        $adherent = $this->adherentFactory->createFromAdminAdherentCreateCommand($command);

        $this->entityManager->persist($adherent);
        $this->entityManager->flush();

        $donationRequest = DonationRequest::createFromAdherent(
            $adherent,
            null,
            $command->getCotisationAmount(),
            PayboxPaymentSubscription::NONE,
            Donation::TYPE_CHECK
        );
        $donationRequest->forMembership();

        $donation = $this->donationRequestHandler->handle($donationRequest, $adherent);
        $donation->markAsFinished();
        $donation->markAsLastSuccessfulDonation();

        $this->entityManager->flush();
    }
}

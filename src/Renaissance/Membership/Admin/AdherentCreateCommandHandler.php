<?php

namespace App\Renaissance\Membership\Admin;

use App\Donation\DonationRequest;
use App\Donation\DonationRequestHandler;
use App\Donation\PayboxPaymentSubscription;
use App\Entity\Donation;
use App\Membership\AdherentFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AdherentCreateCommandHandler
{
    private AdherentFactory $adherentFactory;
    private DonationRequestHandler $donationRequestHandler;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        AdherentFactory $adherentFactory,
        DonationRequestHandler $donationRequestHandler,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->adherentFactory = $adherentFactory;
        $this->donationRequestHandler = $donationRequestHandler;
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    public function createCommand(): AdherentCreateCommand
    {
        return new AdherentCreateCommand();
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
        $donation->markAsLastSuccessfulDonation();

        $this->entityManager->flush();
    }
}

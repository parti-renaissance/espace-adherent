<?php

namespace App\Renaissance\Membership\Admin;

use App\Donation\DonationRequest;
use App\Donation\DonationRequestHandler;
use App\Donation\PayboxPaymentSubscription;
use App\Entity\Administrator;
use App\Entity\Donation;
use App\Membership\AdherentEvents;
use App\Membership\AdherentFactory;
use App\Membership\Event\AdherentAccountWasCreatedEvent;
use App\Membership\Event\UserEvent;
use App\Membership\MembershipSourceEnum;
use App\Membership\UserEvents;
use App\Referent\ReferentTagManager;
use App\Referent\ReferentZoneManager;
use App\Renaissance\Membership\Notifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AdherentCreateCommandHandler
{
    public function __construct(
        private readonly AdherentFactory $adherentFactory,
        private readonly DonationRequestHandler $donationRequestHandler,
        private readonly ReferentTagManager $referentTagManager,
        private readonly ReferentZoneManager $referentZoneManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly Notifier $notifier
    ) {
    }

    public function createCommand(): AdherentCreateCommand
    {
        return new AdherentCreateCommand(MembershipSourceEnum::RENAISSANCE);
    }

    public function handle(AdherentCreateCommand $command, Administrator $administrator): void
    {
        $adherent = $this->adherentFactory->createFromAdminAdherentCreateCommand($command, $administrator);
        $adherent->join();

        $this->entityManager->persist($adherent);

        $this->referentTagManager->assignReferentLocalTags($adherent);
        $this->referentZoneManager->assignZone($adherent);

        $this->entityManager->flush();

        $donationRequest = DonationRequest::createFromAdherent(
            $adherent,
            null,
            $command->getCotisationAmount(),
            PayboxPaymentSubscription::NONE,
            Donation::TYPE_CHECK
        );
        $donationRequest->forMembership();
        $donationRequest->setDonatedAt($command->cotisationDate);

        $donation = $this->donationRequestHandler->handle($donationRequest, $adherent);
        $donation->setDonatedAt($command->cotisationDate);
        $donation->markAsFinished();
        $donation->markAsLastSuccessfulDonation();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UserEvent($adherent, true, true), UserEvents::USER_CREATED);
        $this->dispatcher->dispatch(new AdherentAccountWasCreatedEvent($adherent), AdherentEvents::REGISTRATION_COMPLETED);

        $this->notifier->sendAccountCreatedEmail($adherent);
    }
}

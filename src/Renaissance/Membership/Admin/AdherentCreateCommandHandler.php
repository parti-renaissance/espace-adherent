<?php

namespace App\Renaissance\Membership\Admin;

use App\Donation\DonationRequest;
use App\Donation\DonationRequestHandler;
use App\Donation\PayboxPaymentSubscription;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Donation;
use App\Membership\AdherentEvents;
use App\Membership\AdherentFactory;
use App\Membership\Event\AdherentAccountWasCreatedEvent;
use App\Membership\Event\UserEvent;
use App\Membership\MembershipNotifier;
use App\Membership\MembershipSourceEnum;
use App\Membership\UserEvents;
use App\Referent\ReferentTagManager;
use App\Referent\ReferentZoneManager;
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
        private readonly MembershipNotifier $notifier,
    ) {
    }

    public function createCommand(): AdherentCreateCommand
    {
        return new AdherentCreateCommand(MembershipSourceEnum::RENAISSANCE);
    }

    public function handle(
        AdherentCreateCommand $command,
        Administrator $administrator,
        Adherent $adherent = null
    ): void {
        $forReAdhesion = false;
        if ($adherent) {
            $forReAdhesion = $adherent->isRenaissanceAdherent();
            $adherent->updateMembershipFormAdminAdherentCreateCommand($command, $administrator);
        } else {
            $adherent = $this->adherentFactory->createFromAdminAdherentCreateCommand($command, $administrator);
        }

        $adherent->join();
        $adherent->setPapUserRole(true);

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

        $donation = $this->donationRequestHandler->handle($donationRequest, $adherent, $forReAdhesion);
        $donation->setDonatedAt($command->cotisationDate);
        $donation->markAsFinished();
        $donation->markAsLastSuccessfulDonation();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UserEvent($adherent, true, true), UserEvents::USER_CREATED);
        $this->dispatcher->dispatch(new AdherentAccountWasCreatedEvent($adherent), AdherentEvents::REGISTRATION_COMPLETED);

        if (!$adherent->isEnabled()) {
            $this->notifier->sendAccountCreatedEmail($adherent);
        } elseif ($donation->isReAdhesion()) {
            $this->notifier->sendReAdhesionConfirmationMessage($adherent);
        } else {
            $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_VALIDATED);
            $this->notifier->sendConfirmationJoinMessage($adherent);
        }
    }
}

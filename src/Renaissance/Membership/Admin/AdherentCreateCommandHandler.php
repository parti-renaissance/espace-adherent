<?php

declare(strict_types=1);

namespace App\Renaissance\Membership\Admin;

use App\Adhesion\AdhesionStepEnum;
use App\Donation\Handler\DonationRequestHandler;
use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Donation\Request\DonationRequest;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Donation;
use App\Membership\AdherentFactory;
use App\Membership\Event\UserEvent;
use App\Membership\MembershipNotifier;
use App\Membership\MembershipSourceEnum;
use App\Membership\UserEvents;
use App\Referent\ReferentZoneManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AdherentCreateCommandHandler
{
    public function __construct(
        private readonly AdherentFactory $adherentFactory,
        private readonly DonationRequestHandler $donationRequestHandler,
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
        ?Adherent $adherent = null,
    ): void {
        $forReAdhesion = false;
        if ($adherent) {
            $forReAdhesion = $adherent->isRenaissanceAdherent();
            $adherent->updateMembershipFormAdminAdherentCreateCommand($command, $administrator);
        } else {
            $adherent = $this->adherentFactory->createFromAdminAdherentCreateCommand($command, $administrator);
        }

        $adherent->finishAdhesionStep(AdhesionStepEnum::MAIN_INFORMATION);
        $adherent->setPapUserRole(true);

        $this->entityManager->persist($adherent);

        $this->referentZoneManager->assignZone($adherent);

        $this->entityManager->flush();

        $donationRequest = DonationRequest::create(
            null,
            $command->getCotisationAmount(),
            PayboxPaymentSubscription::NONE,
            $adherent,
            $command->isCotisationTypeTPE() ? Donation::TYPE_TPE : Donation::TYPE_CHECK
        );
        $donationRequest->forMembership();
        $donationRequest->setDonatedAt($command->cotisationDate);

        $donation = $this->donationRequestHandler->handle($donationRequest, $adherent, $forReAdhesion);
        $donation->markAsFinished();
        $donation->markAsLastSuccessfulDonation();

        $this->entityManager->flush();

        $this->dispatcher->dispatch(new UserEvent($adherent, true, true), UserEvents::USER_CREATED);
        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_VALIDATED);

        if (!$adherent->isEnabled()) {
            $this->notifier->sendAccountCreatedEmail($adherent);
        } else {
            $this->notifier->sendConfirmationJoinMessage($adherent, $donation->isReAdhesion());
        }
    }
}

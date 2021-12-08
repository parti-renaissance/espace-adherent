<?php

namespace App\Membership;

use App\Address\PostAddressFactory;
use App\Adherent\Unregistration\UnregistrationCommand;
use App\Adherent\UnregistrationHandler;
use App\Entity\Adherent;
use App\History\EmailSubscriptionHistoryHandler;
use App\Membership\Event\AdherentAccountWasCreatedEvent;
use App\Membership\Event\UserEvent;
use App\Membership\MembershipRequest\CoalitionMembershipRequest;
use App\Membership\MembershipRequest\JeMengageMembershipRequest;
use App\Membership\MembershipRequest\MembershipInterface;
use App\Membership\MembershipRequest\PlatformMembershipRequest;
use App\Referent\ReferentTagManager;
use App\Referent\ReferentZoneManager;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class MembershipRequestHandler
{
    private $dispatcher;
    private $adherentFactory;
    private $addressFactory;
    private $manager;
    private $referentTagManager;
    private $referentZoneManager;
    private $membershipRegistrationProcess;
    private $emailSubscriptionHistoryHandler;
    private $unregistrationHandler;
    private $notifier;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        AdherentFactory $adherentFactory,
        PostAddressFactory $addressFactory,
        ObjectManager $manager,
        ReferentTagManager $referentTagManager,
        ReferentZoneManager $referentZoneManager,
        MembershipRegistrationProcess $membershipRegistrationProcess,
        EmailSubscriptionHistoryHandler $emailSubscriptionHistoryHandler,
        UnregistrationHandler $unregistrationHandler,
        MembershipNotifier $notifier
    ) {
        $this->adherentFactory = $adherentFactory;
        $this->addressFactory = $addressFactory;
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->referentTagManager = $referentTagManager;
        $this->referentZoneManager = $referentZoneManager;
        $this->membershipRegistrationProcess = $membershipRegistrationProcess;
        $this->emailSubscriptionHistoryHandler = $emailSubscriptionHistoryHandler;
        $this->unregistrationHandler = $unregistrationHandler;
        $this->notifier = $notifier;
    }

    public function initialiseMembershipRequest(?string $source): MembershipInterface
    {
        switch ($source) {
            case MembershipSourceEnum::JE_MENGAGE:
                return new JeMengageMembershipRequest();
            case MembershipSourceEnum::COALITIONS:
                return new CoalitionMembershipRequest();
        }

        return new PlatformMembershipRequest();
    }

    public function createAdherent(MembershipInterface $membershipRequest): Adherent
    {
        $this->manager->persist($adherent = $this->adherentFactory->createFromMembershipRequest($membershipRequest));

        $this->referentTagManager->assignReferentLocalTags($adherent);
        $this->referentZoneManager->assignZone($adherent);

        $this->manager->flush();

        $this->dispatcher->dispatch(new UserEvent(
            $adherent,
            $membershipRequest->allowEmailNotifications,
            $membershipRequest->allowMobileNotifications
        ), UserEvents::USER_CREATED);

        if (null === $adherent->getSource() && $adherent->isAdherent()) {
            $this->membershipRegistrationProcess->start($adherent->getUuid()->toString());

            $this->dispatcher->dispatch(new AdherentAccountWasCreatedEvent($adherent, $membershipRequest), AdherentEvents::REGISTRATION_COMPLETED);
        }

        return $adherent;
    }

    public function join(Adherent $user, PlatformMembershipRequest $membershipRequest): void
    {
        $user->updateMembership(
            $membershipRequest,
            $this->addressFactory->createFromAddress($membershipRequest->getAddress())
        );

        $user->join();

        $this->dispatcher->dispatch(new UserEvent(
            $user,
            $membershipRequest->allowEmailNotifications,
            $membershipRequest->allowMobileNotifications
        ), UserEvents::USER_SWITCH_TO_ADHERENT);

        $this->emailSubscriptionHistoryHandler->handleSubscriptions($user);
        $this->updateReferentTagsAndSubscriptionHistoryIfNeeded($user);

        $this->manager->flush();

        $this->notifier->sendConfirmationJoinMessage($user);

        $this->dispatcher->dispatch(new AdherentAccountWasCreatedEvent($user, $membershipRequest), AdherentEvents::REGISTRATION_COMPLETED);
        $this->dispatcher->dispatch(new UserEvent($user), UserEvents::USER_UPDATED);
    }

    /**
     * /!\ Only relevant for update not for creation.
     */
    private function updateReferentTagsAndSubscriptionHistoryIfNeeded(Adherent $adherent): void
    {
        if ($this->referentTagManager->isUpdateNeeded($adherent)) {
            $oldReferentTags = $adherent->getReferentTags()->toArray();
            $this->referentTagManager->assignReferentLocalTags($adherent);
            $this->emailSubscriptionHistoryHandler->handleReferentTagsUpdate($adherent, $oldReferentTags);
        }

        if ($this->referentZoneManager->isUpdateNeeded($adherent)) {
            $this->referentZoneManager->assignZone($adherent);
        }
    }

    public function terminateMembership(
        Adherent $adherent,
        UnregistrationCommand $command = null,
        bool $sendMail = true
    ): void {
        $this->unregistrationHandler->handle($adherent, $command);

        if ($sendMail) {
            $this->notifier->sendUnregistrationMessage($adherent);
        }

        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_DELETED);
    }
}

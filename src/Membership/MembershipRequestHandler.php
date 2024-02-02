<?php

namespace App\Membership;

use App\Address\PostAddressFactory;
use App\Adherent\Unregistration\UnregistrationCommand;
use App\Adherent\UnregistrationHandler;
use App\Entity\Adherent;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\History\EmailSubscriptionHistoryHandler;
use App\Membership\Event\AdherentEvent;
use App\Membership\Event\UserEvent;
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
        return MembershipSourceEnum::JEMENGAGE === $source ? new JeMengageMembershipRequest() : new PlatformMembershipRequest();
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
        }

        $this->dispatcher->dispatch(new AdherentEvent($adherent), AdherentEvents::REGISTRATION_COMPLETED);

        return $adherent;
    }

    public function createOrUpdateRenaissanceAdherent(
        AdherentRequest $adherentRequest,
        ?Adherent $adherent = null
    ): Adherent {
        if ($adherent) {
            $adherent->updateMembershipFromAdherentRequest($adherentRequest);
        } else {
            $adherent = $this->adherentFactory->createFromRenaissanceAdherentRequest($adherentRequest);
        }

        $adherent->enable();
        $adherent->join();
        $adherent->setSource(MembershipSourceEnum::RENAISSANCE);
        $adherent->setPapUserRole(true);

        if (!$adherent->utmSource) {
            $adherent->utmSource = $adherentRequest->utmSource;
            $adherent->utmCampaign = $adherentRequest->utmCampaign;
        }

        $this->manager->persist($adherent);
        $this->referentZoneManager->assignZone($adherent);
        $this->manager->flush();

        $this->dispatcher->dispatch(new UserEvent($adherent, $adherentRequest->allowEmailNotifications, $adherentRequest->allowMobileNotifications), UserEvents::USER_CREATED);
        $this->dispatcher->dispatch(new AdherentEvent($adherent), AdherentEvents::REGISTRATION_COMPLETED);
        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_VALIDATED);

        $adherentRequest->activate();
        $this->manager->flush();

        return $adherent;
    }

    public function finishRenaissanceAdhesion(Adherent $adherent): void
    {
        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_VALIDATED);
        $this->notifier->sendConfirmationJoinMessage($adherent);
    }

    public function finishRenaissanceReAdhesion(Adherent $adherent): void
    {
        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_VALIDATED);
        $this->notifier->sendReAdhesionConfirmationMessage($adherent);
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

        $this->dispatcher->dispatch(new AdherentEvent($user), AdherentEvents::REGISTRATION_COMPLETED);
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
        ?UnregistrationCommand $command = null,
        bool $sendMail = true,
        ?string $comment = null
    ): void {
        $this->unregistrationHandler->handle($adherent, $command, $comment);

        if ($sendMail || ($command && $command->getNotification())) {
            $this->notifier->sendUnregistrationMessage($adherent);
        }

        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_DELETED);
    }
}

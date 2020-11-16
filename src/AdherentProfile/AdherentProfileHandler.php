<?php

namespace App\AdherentProfile;

use App\Address\PostAddressFactory;
use App\Entity\Adherent;
use App\History\EmailSubscriptionHistoryHandler;
use App\Membership\AdherentChangeEmailHandler;
use App\Membership\AdherentEvents;
use App\Membership\AdherentProfileWasUpdatedEvent;
use App\Membership\UserEvent;
use App\Membership\UserEvents;
use App\Referent\ReferentTagManager;
use App\Referent\ReferentZoneManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class AdherentProfileHandler
{
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var EntityManagerInterface */
    private $manager;

    /** @var PostAddressFactory */
    private $addressFactory;

    /** @var AdherentChangeEmailHandler */
    private $emailHandler;

    /** @var ReferentTagManager */
    private $referentTagManager;

    /** @var ReferentZoneManager */
    private $referentZoneManager;

    /** @var EmailSubscriptionHistoryHandler */
    private $emailSubscriptionHistoryHandler;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $manager,
        PostAddressFactory $addressFactory,
        AdherentChangeEmailHandler $emailHandler,
        ReferentTagManager $referentTagManager,
        ReferentZoneManager $referentZoneManager,
        EmailSubscriptionHistoryHandler $emailSubscriptionHistoryHandler
    ) {
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->addressFactory = $addressFactory;
        $this->emailHandler = $emailHandler;
        $this->referentTagManager = $referentTagManager;
        $this->referentZoneManager = $referentZoneManager;
        $this->emailSubscriptionHistoryHandler = $emailSubscriptionHistoryHandler;
    }

    public function update(Adherent $adherent, AdherentProfile $adherentProfile): void
    {
        $this->dispatcher->dispatch(UserEvents::USER_BEFORE_UPDATE, new UserEvent($adherent));

        if ($adherent->getEmailAddress() !== $adherentProfile->getEmailAddress()) {
            $this->emailHandler->handleRequest($adherent, $adherentProfile->getEmailAddress());
        }

        $adherent->updateProfile($adherentProfile, $this->addressFactory->createFromAddress($adherentProfile->getAddress()));
        $this->updateReferentTagsAndSubscriptionHistoryIfNeeded($adherent);

        $this->manager->flush();

        $this->dispatcher->dispatch(AdherentEvents::PROFILE_UPDATED, new AdherentProfileWasUpdatedEvent($adherent));
        $this->dispatcher->dispatch(UserEvents::USER_UPDATED, new UserEvent($adherent));
    }

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
}

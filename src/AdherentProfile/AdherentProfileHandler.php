<?php

namespace App\AdherentProfile;

use App\Address\PostAddressFactory;
use App\Entity\Adherent;
use App\Entity\SubscriptionType;
use App\History\EmailSubscriptionHistoryHandler;
use App\Membership\AdherentChangeEmailHandler;
use App\Membership\AdherentEvents;
use App\Membership\Event\AdherentProfileWasUpdatedEvent;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Referent\ReferentTagManager;
use App\Referent\ReferentZoneManager;
use App\Repository\SubscriptionTypeRepository;
use App\Subscription\SubscriptionHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class AdherentProfileHandler
{
    private EventDispatcherInterface $dispatcher;
    private EntityManagerInterface $manager;
    private PostAddressFactory $addressFactory;
    private AdherentChangeEmailHandler $emailHandler;
    private ReferentTagManager $referentTagManager;
    private ReferentZoneManager $referentZoneManager;
    private EmailSubscriptionHistoryHandler $emailSubscriptionHistoryHandler;
    private SubscriptionTypeRepository $subscriptionTypeRepository;
    private SubscriptionHandler $subscriptionHandler;

    public function __construct(
        EventDispatcherInterface $dispatcher,
        EntityManagerInterface $manager,
        PostAddressFactory $addressFactory,
        AdherentChangeEmailHandler $emailHandler,
        ReferentTagManager $referentTagManager,
        ReferentZoneManager $referentZoneManager,
        EmailSubscriptionHistoryHandler $emailSubscriptionHistoryHandler,
        SubscriptionTypeRepository $subscriptionTypeRepository,
        SubscriptionHandler $subscriptionHandler
    ) {
        $this->dispatcher = $dispatcher;
        $this->manager = $manager;
        $this->addressFactory = $addressFactory;
        $this->emailHandler = $emailHandler;
        $this->referentTagManager = $referentTagManager;
        $this->referentZoneManager = $referentZoneManager;
        $this->emailSubscriptionHistoryHandler = $emailSubscriptionHistoryHandler;
        $this->subscriptionTypeRepository = $subscriptionTypeRepository;
        $this->subscriptionHandler = $subscriptionHandler;
    }

    public function update(Adherent $adherent, AdherentProfile $adherentProfile): void
    {
        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_BEFORE_UPDATE);

        $this->updateSubscriptions($adherent, $adherentProfile->getSubscriptionTypes());

        if ($adherent->getEmailAddress() !== $adherentProfile->getEmailAddress()) {
            $this->emailHandler->handleRequest($adherent, $adherentProfile->getEmailAddress());
        }

        $adherent->updateProfile($adherentProfile, $this->addressFactory->createFromAddress($adherentProfile->getAddress()));

        $this->updateReferentTagsAndSubscriptionHistoryIfNeeded($adherent);

        $this->manager->flush();

        $this->dispatcher->dispatch(new AdherentProfileWasUpdatedEvent($adherent), AdherentEvents::PROFILE_UPDATED);
        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_UPDATED);
    }

    private function updateSubscriptions(Adherent $adherent, array $subscriptionTypeCodes): void
    {
        $oldEmailsSubscriptions = $adherent->getSubscriptionTypes();

        $adherent->setSubscriptionTypes($this->findSubscriptionTypes($subscriptionTypeCodes));

        $this->subscriptionHandler->handleChanges($adherent, $oldEmailsSubscriptions);
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

    /**
     * @param string[]|array $subscriptionTypeCodes
     *
     * @return SubscriptionType[]|array
     */
    private function findSubscriptionTypes(array $subscriptionTypeCodes): array
    {
        return $this->subscriptionTypeRepository->findByCodes($subscriptionTypeCodes);
    }
}

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
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface $manager,
        private readonly PostAddressFactory $addressFactory,
        private readonly AdherentChangeEmailHandler $emailHandler,
        private readonly ReferentTagManager $referentTagManager,
        private readonly ReferentZoneManager $referentZoneManager,
        private readonly EmailSubscriptionHistoryHandler $emailSubscriptionHistoryHandler,
        private readonly SubscriptionTypeRepository $subscriptionTypeRepository,
        private readonly SubscriptionHandler $subscriptionHandler,
    ) {
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

    public function updateReferentTagsAndSubscriptionHistoryIfNeeded(Adherent $adherent): void
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

    private function updateSubscriptions(Adherent $adherent, array $subscriptionTypeCodes): void
    {
        $oldEmailsSubscriptions = $adherent->getSubscriptionTypes();
        $newEmailsSubscriptions = $this->findSubscriptionTypes($subscriptionTypeCodes);

        if (array_diff($oldEmailsSubscriptions, $newEmailsSubscriptions)) {
            $adherent->setSubscriptionTypes($newEmailsSubscriptions);
            $this->subscriptionHandler->handleChanges($adherent, $oldEmailsSubscriptions);
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

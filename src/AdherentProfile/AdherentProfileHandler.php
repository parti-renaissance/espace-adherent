<?php

namespace App\AdherentProfile;

use App\Address\PostAddressFactory;
use App\Entity\Adherent;
use App\Entity\SubscriptionType;
use App\Membership\AdherentChangeEmailHandler;
use App\Membership\AdherentEvents;
use App\Membership\Event\AdherentEvent;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
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
        private readonly ReferentZoneManager $referentZoneManager,
        private readonly SubscriptionTypeRepository $subscriptionTypeRepository,
        private readonly SubscriptionHandler $subscriptionHandler,
    ) {
    }

    public function update(Adherent $adherent, AdherentProfile $adherentProfile): void
    {
        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_BEFORE_UPDATE);
        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_PROFILE_BEFORE_UPDATE);

        $this->updateSubscriptions($adherent, $adherentProfile->getSubscriptionTypes());

        if ($adherent->getEmailAddress() !== $adherentProfile->getEmailAddress()) {
            $this->emailHandler->handleRequest($adherent, $adherentProfile->getEmailAddress());
        }

        $adherent->updateProfile($adherentProfile, $this->addressFactory->createFromAddress($adherentProfile->getPostAddress()));

        $this->updateReferentTagsAndSubscriptionHistoryIfNeeded($adherent);

        $this->manager->flush();

        $this->dispatcher->dispatch(new AdherentEvent($adherent), AdherentEvents::PROFILE_UPDATED);
        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_UPDATED);
        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_PROFILE_AFTER_UPDATE);
    }

    public function updateReferentTagsAndSubscriptionHistoryIfNeeded(Adherent $adherent): void
    {
        if ($this->referentZoneManager->isUpdateNeeded($adherent)) {
            $this->referentZoneManager->assignZone($adherent);
        }
    }

    private function updateSubscriptions(Adherent $adherent, array $subscriptionTypeCodes): void
    {
        $oldEmailsSubscriptions = $adherent->getSubscriptionTypes();
        $newEmailsSubscriptions = $this->findSubscriptionTypes($subscriptionTypeCodes);

        if (array_diff($oldEmailsSubscriptions, $newEmailsSubscriptions) || array_diff($newEmailsSubscriptions, $oldEmailsSubscriptions)) {
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

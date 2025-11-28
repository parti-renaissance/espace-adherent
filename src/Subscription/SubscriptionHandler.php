<?php

declare(strict_types=1);

namespace App\Subscription;

use App\Entity\Adherent;
use App\History\EmailSubscriptionHistoryHandler;
use App\Repository\SubscriptionTypeRepository;
use Doctrine\ORM\EntityManagerInterface;

class SubscriptionHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EmailSubscriptionHistoryHandler $subscriptionHistoryHandler,
        private readonly SubscriptionTypeRepository $subscriptionTypeRepository,
    ) {
    }

    public function addDefaultTypesToAdherent(Adherent $adherent, bool $allowEmailNotifications, bool $allowMobileNotifications): void
    {
        $this->handleUpdateSubscription($adherent, array_merge(
            $allowEmailNotifications ? $this->getEmailDefaultTypes($adherent) : [],
            $allowMobileNotifications ? SubscriptionTypeEnum::DEFAULT_MOBILE_TYPES : []
        ));
    }

    public function handleUpdateSubscription(Adherent $adherent, array $newSubscriptionCodes): void
    {
        $oldSubscriptionTypes = $adherent->getSubscriptionTypes();
        $newSubscriptionTypes = $newSubscriptionCodes ? $this->subscriptionTypeRepository->findByCodes($newSubscriptionCodes) : [];

        if (array_diff($oldSubscriptionTypes, $newSubscriptionTypes) || array_diff($newSubscriptionTypes, $oldSubscriptionTypes)) {
            $adherent->setSubscriptionTypes($newSubscriptionTypes);
            $this->em->flush();

            $this->subscriptionHistoryHandler->handleSubscriptionsUpdate($adherent, $oldSubscriptionTypes);
        }
    }

    private function getEmailDefaultTypes(Adherent $adherent): array
    {
        $types = SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES;

        if ($adherent->getAge() && $adherent->getAge() < 35) {
            $types[] = SubscriptionTypeEnum::JAM_EMAIL;
        }

        return $types;
    }
}

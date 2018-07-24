<?php

namespace AppBundle\Subscription;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\SubscriptionTypeRepository;

class SubscriptionHandler
{
    private $repository;

    public function __construct(SubscriptionTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function addDefaultTypesToAdherent(
        Adherent $adherent,
        bool $allowEmailNotifications,
        bool $allowMobileNotifications
    ): void {
        $this->repository->addToAdherent(
            $adherent,
            array_merge(
                $allowEmailNotifications ? $this->getEmailDefaultTypes($adherent) : [],
                $allowMobileNotifications ? SubscriptionTypeEnum::DEFAULT_MOBILE_TYPES : []
            )
        );
    }

    private function getEmailDefaultTypes(Adherent $adherent): array
    {
        if ($adherent->isAdherent()) {
            return SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES;
        }

        return SubscriptionTypeEnum::USER_TYPES;
    }
}

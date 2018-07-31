<?php

namespace AppBundle\Subscription;

use AppBundle\Entity\Adherent;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\SubscriptionTypeRepository;

class SubscriptionHandler
{
    public const ACTION_TYPE_SUBSCRIBE = 'subscribe';
    public const ACTION_TYPE_UNSUBSCRIBE = 'unsubscribe';
    public const ACTION_TYPES = [
      self::ACTION_TYPE_SUBSCRIBE,
      self::ACTION_TYPE_UNSUBSCRIBE,
    ];

    private $adherentRepository;
    private $subscriptionTypeRepository;

    public function __construct(AdherentRepository $adherentRepository, SubscriptionTypeRepository $subscriptionTypeRepository)
    {
        $this->adherentRepository = $adherentRepository;
        $this->subscriptionTypeRepository = $subscriptionTypeRepository;
    }

    public function changeSubscription(string $type, string $email, string $listId): void
    {
        if (!\in_array($type, self::ACTION_TYPES)) {
            throw new \InvalidArgumentException('Action is not authorised.');
        }

        $adherent = $this->adherentRepository->findOneByEmail($email);
        if (!$adherent) {
            throw new \RuntimeException(sprintf('There is no adherent with email address "%s".', $email));
        }

        $subscriptionType = $this->subscriptionTypeRepository->findOneByExternalId($listId);
        if (!$subscriptionType) {
            throw new \RuntimeException(sprintf('There is no subscription type with external service id "%s".', $listId));
        }

        $hasSubscription = $adherent->hasSubscriptionType($subscriptionType->getCode());
        if (self::ACTION_TYPE_SUBSCRIBE === $type && !$hasSubscription) {
            $adherent->addSubscriptionType($subscriptionType);
        } elseif (self::ACTION_TYPE_UNSUBSCRIBE === $type && $hasSubscription) {
            $adherent->removeSubscriptionType($subscriptionType);
        }
    }

    public function addDefaultTypesToAdherent(
        Adherent $adherent,
        bool $allowEmailNotifications,
        bool $allowMobileNotifications
    ): void {
        $this->subscriptionTypeRepository->addToAdherent(
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

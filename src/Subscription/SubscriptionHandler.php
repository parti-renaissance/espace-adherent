<?php

namespace App\Subscription;

use App\Entity\Adherent;
use App\History\EmailSubscriptionHistoryHandler;
use App\Membership\Event\UserEvent;
use App\Membership\UserEvents;
use App\Repository\AdherentRepository;
use App\Repository\NewsletterSubscriptionRepository;
use App\Repository\SubscriptionTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SubscriptionHandler
{
    public const ACTION_TYPE_SUBSCRIBE = 'subscribe';
    public const ACTION_TYPE_UNSUBSCRIBE = 'unsubscribe';
    public const ACTION_TYPES = [
        self::ACTION_TYPE_SUBSCRIBE,
        self::ACTION_TYPE_UNSUBSCRIBE,
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EmailSubscriptionHistoryHandler $subscriptionHistoryHandler,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly AdherentRepository $adherentRepository,
        private readonly SubscriptionTypeRepository $subscriptionTypeRepository,
        private readonly NewsletterSubscriptionRepository $newsletterSubscriptionRepository,
    ) {
    }

    public function handleChanges(Adherent $adherent, array $oldEmailsSubscriptions): void
    {
        $this->subscriptionHistoryHandler->handleSubscriptionsUpdate($adherent, $oldEmailsSubscriptions);
        $this->dispatcher->dispatch(new UserEvent($adherent), UserEvents::USER_UPDATE_SUBSCRIPTIONS);
    }

    public function changeSubscription(string $type, string $email, string $listId): void
    {
        if (!\in_array($type, self::ACTION_TYPES)) {
            throw new \InvalidArgumentException('Action is not authorised.');
        }

        $adherent = $this->adherentRepository->findOneByEmail($email);
        if ($adherent) {
            $subscriptionType = $this->subscriptionTypeRepository->findOneByExternalId($listId);
            if (!$subscriptionType) {
                throw new \RuntimeException(\sprintf('There is no subscription type with external service id "%s".', $listId));
            }

            $hasSubscription = $adherent->hasSubscriptionType($subscriptionType->getCode());
            if (self::ACTION_TYPE_SUBSCRIBE === $type && !$hasSubscription) {
                $adherent->addSubscriptionType($subscriptionType);
            } elseif (self::ACTION_TYPE_UNSUBSCRIBE === $type && $hasSubscription) {
                $adherent->removeSubscriptionType($subscriptionType);
            }
        } elseif (($newsletterSubscription = $this->newsletterSubscriptionRepository->findOneByEmail($email))
            && self::ACTION_TYPE_UNSUBSCRIBE === $type) {
            // Newsletter subscription will remain in the table but with filled `deleted_at`field
            $this->em->remove($newsletterSubscription);
        }

        $this->em->flush();
    }

    public function addDefaultTypesToAdherent(
        Adherent $adherent,
        bool $allowEmailNotifications,
        bool $allowMobileNotifications,
    ): void {
        $this->subscriptionTypeRepository->addToAdherent(
            $adherent,
            array_merge(
                $allowEmailNotifications ? $this->getEmailDefaultTypes($adherent) : [],
                $allowMobileNotifications ? SubscriptionTypeEnum::DEFAULT_MOBILE_TYPES : []
            )
        );
        $this->em->flush();
    }

    private function getEmailDefaultTypes(Adherent $adherent): array
    {
        if ($adherent->isAdherent()) {
            $types = SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES;
        } else {
            $types = SubscriptionTypeEnum::USER_TYPES;
        }

        if ($adherent->getAge() && $adherent->getAge() < 35) {
            $types[] = SubscriptionTypeEnum::JAM_EMAIL;
        }

        return $types;
    }

    public function handleUpdateSubscription(Adherent $adherent, array $newSubscriptionCodes): void
    {
        $oldSubscriptionTypes = $adherent->getSubscriptionTypes();

        $adherent->setSubscriptionTypes($this->subscriptionTypeRepository->findByCodes($newSubscriptionCodes));

        $this->em->flush();

        $this->subscriptionHistoryHandler->handleSubscriptionsUpdate($adherent, $oldSubscriptionTypes);
    }
}

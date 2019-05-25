<?php

namespace AppBundle\Subscription;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\NewsletterSubscription;
use AppBundle\Entity\SubscriptionType;
use AppBundle\History\EmailSubscriptionHistoryHandler;
use Doctrine\ORM\EntityManagerInterface;

class SubscriptionHandler
{
    public const ACTION_TYPE_SUBSCRIBE = 'subscribe';
    public const ACTION_TYPE_UNSUBSCRIBE = 'unsubscribe';
    public const ACTION_TYPES = [
      self::ACTION_TYPE_SUBSCRIBE,
      self::ACTION_TYPE_UNSUBSCRIBE,
    ];

    private $em;
    private $adherentRepository;
    private $subscriptionTypeRepository;
    private $newsletterSubscriptionRepository;
    private $subscriptionHistoryHandler;

    public function __construct(EntityManagerInterface $em, EmailSubscriptionHistoryHandler $subscriptionHistoryHandler)
    {
        $this->em = $em;
        $this->subscriptionHistoryHandler = $subscriptionHistoryHandler;

        $this->adherentRepository = $this->em->getRepository(Adherent::class);
        $this->subscriptionTypeRepository = $this->em->getRepository(SubscriptionType::class);
        $this->newsletterSubscriptionRepository = $this->em->getRepository(NewsletterSubscription::class);
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
                throw new \RuntimeException(sprintf('There is no subscription type with external service id "%s".', $listId));
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

    public function unsubscribeAllMails(Adherent $adherent): void
    {
        $this->subscriptionHistoryHandler->handleUnsubscriptions($adherent);

        $adherent->setSubscriptionTypes([]);

        $this->em->flush();
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

    public function handleUpdateSubscription(Adherent $adherent, array $newSubscriptionCodes): void
    {
        $oldSubscriptionTypes = $adherent->getSubscriptionTypes();

        $this->subscriptionTypeRepository->addToAdherent($adherent, $newSubscriptionCodes);

        $this->subscriptionHistoryHandler->handleSubscriptionsUpdate($adherent, $oldSubscriptionTypes);
    }
}

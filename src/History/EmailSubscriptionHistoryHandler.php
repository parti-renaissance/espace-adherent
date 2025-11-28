<?php

declare(strict_types=1);

namespace App\History;

use App\Entity\Adherent;
use App\Entity\Reporting\EmailSubscriptionHistory;
use App\Entity\Reporting\EmailSubscriptionHistoryAction;
use App\Repository\EmailSubscriptionHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;

class EmailSubscriptionHistoryHandler
{
    private $em;
    private $historyRepository;

    public function __construct(EntityManagerInterface $em, EmailSubscriptionHistoryRepository $historyRepository)
    {
        $this->em = $em;
        $this->historyRepository = $historyRepository;
    }

    /**
     * Useful on email subscription settings update
     *
     * - $adherent is up-to-date with new email subscriptions
     * - $oldEmailsSubscriptions is basically the email subscriptions that we have in DB
     *
     * => New and old subscriptions are compared and only the relevant subscription histories are created.
     */
    public function handleSubscriptionsUpdate(Adherent $adherent, array $oldEmailsSubscriptions): void
    {
        $subscriptions = array_diff($adherent->getSubscriptionTypes(), $oldEmailsSubscriptions);
        $unsubscriptions = array_diff($oldEmailsSubscriptions, $adherent->getSubscriptionTypes());

        $this->createEmailSubscriptionHistory($adherent, $subscriptions, EmailSubscriptionHistoryAction::SUBSCRIBE());
        $this->createEmailSubscriptionHistory($adherent, $unsubscriptions, EmailSubscriptionHistoryAction::UNSUBSCRIBE());

        $this->em->flush();
    }

    public function handleSubscriptions(Adherent $adherent): void
    {
        $this->createEmailSubscriptionHistory(
            $adherent,
            $adherent->getSubscriptionTypes(),
            EmailSubscriptionHistoryAction::SUBSCRIBE()
        );
    }

    public function handleUnsubscriptions(Adherent $adherent): void
    {
        $this->createEmailSubscriptionHistory(
            $adherent,
            $adherent->getSubscriptionTypes(),
            EmailSubscriptionHistoryAction::UNSUBSCRIBE()
        );
    }

    private function createEmailSubscriptionHistory(
        Adherent $adherent,
        array $subscriptions,
        EmailSubscriptionHistoryAction $action,
    ): void {
        foreach ($subscriptions as $subscription) {
            $this->em->persist(new EmailSubscriptionHistory($adherent, $subscription, $action));
        }
        $this->em->flush();
    }
}

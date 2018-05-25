<?php

namespace AppBundle\History;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Reporting\EmailSubscriptionHistory;
use AppBundle\Entity\Reporting\EmailSubscriptionHistoryAction;
use AppBundle\Membership\AdherentEmailSubscription;
use AppBundle\Repository\EmailSubscriptionHistoryRepository;
use Cake\Chronos\Chronos;
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
        $subscriptions = array_diff($adherent->getEmailsSubscriptions(), $oldEmailsSubscriptions);
        $unsubscriptions = array_diff($oldEmailsSubscriptions, $adherent->getEmailsSubscriptions());
        $referentTags = $adherent->getReferentTags()->toArray();

        $this->createEmailSubscriptionHistory($adherent, $subscriptions, $referentTags, EmailSubscriptionHistoryAction::SUBSCRIBE());
        $this->createEmailSubscriptionHistory($adherent, $unsubscriptions, $referentTags, EmailSubscriptionHistoryAction::UNSUBSCRIBE());

        $this->em->flush();
    }

    /**
     * Useful on address update
     *
     * - $adherent is up-to-date with new referentTags
     * - $oldReferentTags is basically the referent tags that we have in DB
     *
     * => New and old referent tags are compared and only the relevant subscription histories are created.
     */
    public function handleReferentTagsUpdate(Adherent $adherent, array $oldReferentTags): void
    {
        $subscribedReferentTags = array_diff($adherent->getReferentTags()->toArray(), $oldReferentTags);
        $unsubscribedReferentTags = array_diff($oldReferentTags, $adherent->getReferentTags()->toArray());
        $subscriptions = $adherent->getEmailsSubscriptions();

        $this->createEmailSubscriptionHistory($adherent, $subscriptions, $subscribedReferentTags, EmailSubscriptionHistoryAction::SUBSCRIBE());
        $this->createEmailSubscriptionHistory($adherent, $subscriptions, $unsubscribedReferentTags, EmailSubscriptionHistoryAction::UNSUBSCRIBE());
    }

    public function handleSubscriptions(Adherent $adherent): void
    {
        $this->createEmailSubscriptionHistory(
            $adherent,
            $adherent->getEmailsSubscriptions(),
            $adherent->getReferentTags()->toArray(),
            EmailSubscriptionHistoryAction::SUBSCRIBE()
        );
    }

    public function handleUnsubscriptions(Adherent $adherent): void
    {
        $this->createEmailSubscriptionHistory(
            $adherent,
            $adherent->getEmailsSubscriptions(),
            $adherent->getReferentTags()->toArray(),
            EmailSubscriptionHistoryAction::UNSUBSCRIBE()
        );
    }

    private function createEmailSubscriptionHistory(
        Adherent $adherent,
        array $subscriptions,
        array $referentTags,
        EmailSubscriptionHistoryAction $action
    ): void {
        foreach ($subscriptions as $subscription) {
            $this->em->persist(
                new EmailSubscriptionHistory($adherent, $subscription, $referentTags, $action)
            );
        }
    }

    public function queryCountByMonth(Adherent $referent, int $months = 6): array
    {
        foreach (range(0, $months - 1) as $month) {
            $until = (new Chronos("last day of -$month month"))->setTime(23, 59, 59, 999);

            $subscriptions = $this->historyRepository->countAllByTypeForReferentManagedArea(
                $referent,
                [
                    AdherentEmailSubscription::SUBSCRIBED_EMAILS_LOCAL_HOST,
                    AdherentEmailSubscription::SUBSCRIBED_EMAILS_REFERENTS,
                ],
                $until
            );

            $countByMonth[$until->format('Y-m')] = $subscriptions;
        }

        return $countByMonth;
    }
}

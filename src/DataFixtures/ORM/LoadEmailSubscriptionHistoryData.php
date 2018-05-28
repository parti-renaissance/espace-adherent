<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Reporting\EmailSubscriptionHistory;
use AppBundle\Entity\Reporting\EmailSubscriptionHistoryAction;
use AppBundle\Membership\AdherentEmailSubscription;
use AppBundle\Repository\AdherentRepository;
use Cake\Chronos\Chronos;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEmailSubscriptionHistoryData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var ObjectManager
     */
    private $manager;

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->adherentRepository = $manager->getRepository(Adherent::class);
        $adherents = $this->adherentRepository->findAll();

        // Create current subscription history
        foreach ($adherents as $adherent) {
            foreach ($adherent->getEmailsSubscriptions() as $subscription) {
                $manager->persist(new EmailSubscriptionHistory($adherent, $subscription, $adherent->getReferentTags()->toArray(), EmailSubscriptionHistoryAction::SUBSCRIBE()));
            }
        }

        /*
         * Create some old subscription history for testing
         */

        // Create 2 history lines while it could be one, why?
        // It's done on purpose to make sure stats are calculated correctly in the case where
        // one updates his address and the new one have common referent tag(s) with the old one (it can happen with paris district for example)
        $this->createSubscribedUnsubscribedHistory(
            $this->getReference('adherent-3'),
            [$this->getReference('referent_tag_75')],
            '-5 months'
        );
        $this->createSubscribedUnsubscribedHistory(
            $this->getReference('adherent-3'),
            [$this->getReference('referent_tag_75008')],
            '-5 months'
        );

        $this->createSubscribedUnsubscribedHistory(
            $this->getReference('adherent-4'),
            [$this->getReference('referent_tag_75'), $this->getReference('referent_tag_75009')],
            '-4 months'
        );

        $this->createSubscribedUnsubscribedHistory(
            $this->getReference('adherent-7'),
            [$this->getReference('referent_tag_77')],
            '-3 months'
        );

        $this->createSubscribedUnsubscribedHistory(
            $this->getReference('adherent-17'),
            [$this->getReference('referent_tag_75'), $this->getReference('referent_tag_75008')],
            '-2 months'
        );

        $manager->flush();
    }

    private function createSubscribedUnsubscribedHistory(
        Adherent $adherent,
        array $tags,
        string $subscribedAt,
        string $unsubscribedAt = '-1 month',
        string $subscriptionType = AdherentEmailSubscription::SUBSCRIBED_EMAILS_LOCAL_HOST
    ): void {
        $this->manager->persist(new EmailSubscriptionHistory(
            $adherent,
            $subscriptionType,
            $tags,
            EmailSubscriptionHistoryAction::SUBSCRIBE(),
            new Chronos($subscribedAt)
        ));

        $this->manager->persist(new EmailSubscriptionHistory(
            $adherent,
            $subscriptionType,
            $tags,
            EmailSubscriptionHistoryAction::UNSUBSCRIBE(),
            new Chronos($unsubscribedAt)
        ));
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadReferentTagData::class,
        ];
    }
}

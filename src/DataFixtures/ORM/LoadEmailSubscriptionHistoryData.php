<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentTag;
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
                foreach ($adherent->getReferentTags() as $tag) {
                    $manager->persist(new EmailSubscriptionHistory($adherent, $subscription, $tag, EmailSubscriptionHistoryAction::SUBSCRIBE()));
                }
            }
        }

        // Create some old subscription history for testing
        $this->createSubscribedUnsubscribedHistory($this->getReference('adherent-3'), $this->getReference('referent_tag_75'), '-5 months');
        $this->createSubscribedUnsubscribedHistory($this->getReference('adherent-3'), $this->getReference('referent_tag_75008'), '-5 months');

        $this->createSubscribedUnsubscribedHistory($this->getReference('adherent-4'), $this->getReference('referent_tag_75'), '-4 months');
        $this->createSubscribedUnsubscribedHistory($this->getReference('adherent-4'), $this->getReference('referent_tag_75009'), '-4 months');

        $this->createSubscribedUnsubscribedHistory($this->getReference('adherent-7'), $this->getReference('referent_tag_77'), '-3 months');

        $this->createSubscribedUnsubscribedHistory($this->getReference('adherent-17'), $this->getReference('referent_tag_75'), '-2 months');
        $this->createSubscribedUnsubscribedHistory($this->getReference('adherent-17'), $this->getReference('referent_tag_75008'), '-2 months');

        $manager->flush();
    }

    private function createSubscribedUnsubscribedHistory(
        Adherent $adherent,
        ReferentTag $tag,
        string $subscribedAt,
        string $unsubscribedAt = '-1 month',
        string $subscriptionType = AdherentEmailSubscription::SUBSCRIBED_EMAILS_LOCAL_HOST
    ): void {
        $this->manager->persist(new EmailSubscriptionHistory(
            $adherent,
            $subscriptionType,
            $tag,
            EmailSubscriptionHistoryAction::SUBSCRIBE(),
            new Chronos($subscribedAt)
        ));

        $this->manager->persist(new EmailSubscriptionHistory(
            $adherent,
            $subscriptionType,
            $tag,
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

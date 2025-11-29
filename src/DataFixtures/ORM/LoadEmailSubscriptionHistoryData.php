<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Reporting\EmailSubscriptionHistory;
use App\Entity\Reporting\EmailSubscriptionHistoryAction;
use App\Entity\SubscriptionType;
use App\Repository\AdherentRepository;
use App\Subscription\SubscriptionTypeEnum;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadEmailSubscriptionHistoryData extends Fixture implements DependentFixtureInterface
{
    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var ObjectManager
     */
    private $manager;

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;
        $this->adherentRepository = $manager->getRepository(Adherent::class);
        $adherents = $this->adherentRepository->findAll();

        // Create current subscription history
        foreach ($adherents as $adherent) {
            foreach ($adherent->getSubscriptionTypes() as $subscription) {
                $manager->persist(new EmailSubscriptionHistory($adherent, $subscription, EmailSubscriptionHistoryAction::SUBSCRIBE()));
            }
        }

        /*
         * Create some old subscription history for testing
         */
        Chronos::setTestNow('2018-04-10');

        // Create 2 history lines while it could be one, why?
        // It's done on purpose to make sure stats are calculated correctly in the case where
        // one updates his address and the new one have common referent tag(s) with the old one (it can happen with paris district for example)
        $this->createSubscribedUnsubscribedHistory(
            $this->getReference('adherent-3', Adherent::class),
            '-5 months'
        );
        $this->createSubscribedUnsubscribedHistory(
            $this->getReference('adherent-3', Adherent::class),
            '-5 months'
        );

        $this->createSubscribedUnsubscribedHistory(
            $this->getReference('adherent-4', Adherent::class),
            '-4 months'
        );

        $this->createSubscribedUnsubscribedHistory(
            $this->getReference('adherent-7', Adherent::class),
            '-3 months'
        );

        $this->createSubscribedUnsubscribedHistory(
            $this->getReference('adherent-17', Adherent::class),
            '-2 months'
        );

        Chronos::setTestNow();

        $manager->flush();
    }

    private function createSubscribedUnsubscribedHistory(Adherent $adherent, string $subscribedAt): void
    {
        $this->manager->persist(new EmailSubscriptionHistory(
            $adherent,
            $this->getReference('st-'.SubscriptionTypeEnum::LOCAL_HOST_EMAIL, SubscriptionType::class),
            EmailSubscriptionHistoryAction::SUBSCRIBE(),
            new Chronos($subscribedAt)
        ));

        $this->manager->persist(new EmailSubscriptionHistory(
            $adherent,
            $this->getReference('st-'.SubscriptionTypeEnum::LOCAL_HOST_EMAIL, SubscriptionType::class),
            EmailSubscriptionHistoryAction::UNSUBSCRIBE(),
            new Chronos('-1 month')
        ));
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}

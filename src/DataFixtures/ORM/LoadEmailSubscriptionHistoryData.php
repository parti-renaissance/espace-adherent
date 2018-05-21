<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Reporting\EmailSubscriptionHistory;
use AppBundle\Entity\Reporting\EmailSubscriptionHistoryAction;
use Cake\Chronos\Chronos;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadEmailSubscriptionHistoryData extends AbstractFixture implements DependentFixtureInterface
{
    private $adherentRepository;

    public function load(ObjectManager $manager)
    {
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
        $adherents = [
            $this->getReference('adherent-2'),
            $this->getReference('adherent-4'),
            $this->getReference('adherent-13'),
        ];

        foreach ($adherents as $adherent) {
            foreach ($adherent->getEmailsSubscriptions() as $subscription) {
                foreach ($adherent->getReferentTags() as $tag) {
                    $manager->persist(new EmailSubscriptionHistory($adherent, $subscription, $tag, EmailSubscriptionHistoryAction::SUBSCRIBE(), new Chronos('-5 months')));
                    $manager->persist(new EmailSubscriptionHistory($adherent, $subscription, $tag, EmailSubscriptionHistoryAction::UNSUBSCRIBE(), new Chronos('-3 months')));
                }
            }
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadReferentTagData::class,
        ];
    }
}

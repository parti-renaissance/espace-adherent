<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\Thread;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaThreadData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $adherent2 = $this->getReference('adherent-2');
        $adherent4 = $this->getReference('adherent-4');
        $adherent5 = $this->getReference('adherent-5');

        $threadAQProblemAdherent2 = new Thread(
            'J\'ouvre une discussion sur le problème.',
            $adherent2,
            $this->getReference('answer-q-problem')
        );
        $this->setReference('thread-aq-problem', $threadAQProblemAdherent2);

        $threadAQAnswerAdherent4 = new Thread(
            'J\'ouvre une discussion sur la solution.',
            $adherent4,
            $this->getReference('answer-q-answer')
        );
        $this->setReference('thread-aq-answer', $threadAQAnswerAdherent4);

        $threadAQCompareAdherent5 = new Thread(
            'J\'ouvre une discussion sur la comparaison.',
            $adherent5,
            $this->getReference('answer-q-compare')
        );
        $this->setReference('thread-aq-compare', $threadAQCompareAdherent5);

        $manager->persist($threadAQProblemAdherent2);
        $manager->persist($threadAQAnswerAdherent4);
        $manager->persist($threadAQCompareAdherent5);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadIdeaAnswerData::class,
        ];
    }
}

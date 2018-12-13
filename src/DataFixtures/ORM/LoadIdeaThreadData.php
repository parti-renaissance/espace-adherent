<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\AutoIncrementResetter;
use AppBundle\Entity\IdeasWorkshop\Thread;
use AppBundle\Entity\IdeasWorkshop\ThreadCommentStatusEnum;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaThreadData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'ideas_workshop_thread');

        $adherent2 = $this->getReference('adherent-2');
        $adherent4 = $this->getReference('adherent-4');
        $adherent5 = $this->getReference('adherent-5');

        $threadAQProblemAdherent2 = new Thread(
            'J\'ouvre une discussion sur le problème.',
            $adherent2,
            $this->getReference('answer-q-problem'),
            ThreadCommentStatusEnum::POSTED,
            new \DateTime('-1 minute')
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

        $threadRefused = new Thread(
            'Une discussion refusée.',
            $adherent5,
            $this->getReference('answer-q-compare'),
            ThreadCommentStatusEnum::REFUSED
        );

        $threadReported = new Thread(
            'Une discussion signalée.',
            $adherent5,
            $this->getReference('answer-q-compare'),
            ThreadCommentStatusEnum::REPORTED
        );

        $threadHE = new Thread(
            '[Help Ecology] J\'ouvre une discussion sur le problème.',
            $adherent5,
            $this->getReference('answer-q-problem-idea-he')
        );

        $manager->persist($threadAQProblemAdherent2);
        $manager->persist($threadAQAnswerAdherent4);
        $manager->persist($threadAQCompareAdherent5);
        $manager->persist($threadRefused);
        $manager->persist($threadReported);
        $manager->persist($threadHE);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadIdeaAnswerData::class,
        ];
    }
}

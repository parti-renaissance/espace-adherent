<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaThreadCommentData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $threadAQProblemAdherent2 = $this->getReference('thread-aq-problem');
        $threadAQCompareAdherent5 = $this->getReference('thread-aq-compare');

        $commentFromAdherent6 = new ThreadComment(
            'Aenean viverra efficitur lorem',
            $this->getReference('adherent-6'),
            $threadAQProblemAdherent2
        );

        $commentFromAdherent7 = new ThreadComment(
            'Lorem Ipsum Commentaris',
            $this->getReference('adherent-7'),
            $threadAQProblemAdherent2
        );

        $commentFromAdherent8 = new ThreadComment(
            'Lorem Ipsum Commentaris',
            $this->getReference('adherent-8'),
            $threadAQProblemAdherent2
        );

        $commentFromAdherent9 = new ThreadComment(
            'Aenean viverra efficitur lorem',
            $this->getReference('adherent-9'),
            $threadAQCompareAdherent5
        );

        $manager->persist($commentFromAdherent6);
        $manager->persist($commentFromAdherent7);
        $manager->persist($commentFromAdherent8);
        $manager->persist($commentFromAdherent9);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadIdeaThreadData::class,
        ];
    }
}

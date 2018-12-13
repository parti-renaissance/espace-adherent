<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\AutoIncrementResetter;
use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use AppBundle\Entity\IdeasWorkshop\ThreadCommentStatusEnum;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaThreadCommentData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'ideas_workshop_comment');

        $threadAQProblemAdherent2 = $this->getReference('thread-aq-problem');
        $threadAQCompareAdherent5 = $this->getReference('thread-aq-compare');

        $commentFromAdherent6 = new ThreadComment(
            'Aenean viverra efficitur lorem',
            $this->getReference('adherent-6'),
            $threadAQProblemAdherent2,
            ThreadCommentStatusEnum::POSTED,
            new \DateTime('-4 minutes')
        );

        $commentFromAdherent7 = new ThreadComment(
            'Lorem Ipsum Commentaris',
            $this->getReference('adherent-7'),
            $threadAQProblemAdherent2,
            ThreadCommentStatusEnum::POSTED,
            new \DateTime('-3 minutes')
        );

        $commentFromAdherent8 = new ThreadComment(
            'Commentaire d\'un référent',
            $this->getReference('adherent-8'),
            $threadAQProblemAdherent2,
            ThreadCommentStatusEnum::POSTED,
            new \DateTime('-2 minutes')
        );

        $commentFromAdherent9 = new ThreadComment(
            'Commentaire de Laura',
            $this->getReference('adherent-9'),
            $threadAQCompareAdherent5,
            ThreadCommentStatusEnum::POSTED,
            new \DateTime('-1 minute')
        );

        $commentRefused = new ThreadComment(
            'Commentaire refusé',
            $this->getReference('adherent-9'),
            $threadAQCompareAdherent5,
            ThreadCommentStatusEnum::REFUSED
        );

        $commentReported = new ThreadComment(
            'Commentaire signalé',
            $this->getReference('adherent-9'),
            $threadAQCompareAdherent5,
            ThreadCommentStatusEnum::REPORTED
        );

        $manager->persist($commentFromAdherent6);
        $manager->persist($commentFromAdherent7);
        $manager->persist($commentFromAdherent8);
        $manager->persist($commentFromAdherent9);
        $manager->persist($commentRefused);
        $manager->persist($commentReported);

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

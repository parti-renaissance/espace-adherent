<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\ThreadComment;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaCommentData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $thread = $this->getReference('thread');

        $commentFromAdherent8 = new ThreadComment(
            'Lorem Ipsum Commentaris',
            $this->getReference('adherent-8'),
            $thread
        );

        $commentFromAdherent9 = new ThreadComment(
            'Aenean viverra efficitur lorem',
            $this->getReference('adherent-9'),
            $thread
        );

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

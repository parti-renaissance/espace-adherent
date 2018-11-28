<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\Comment;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaCommentData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $adherent8 = $this->getReference('adherent-8');
        $adherent9 = $this->getReference('adherent-9');
        $thread = $this->getReference('thread');

        $commentFromAdherent9 = new Comment(
            'Lorem Ipsum Commentaris',
            $adherent8,
            $thread
        );

        $commentFromAdherent8 = new Comment(
            'Lorem Ipsum Commentaris',
            $adherent9,
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

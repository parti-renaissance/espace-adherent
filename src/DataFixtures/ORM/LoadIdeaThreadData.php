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
        $answerAdherent1 = $this->getReference('answer-lorem-adherent-1');

        $thread = new Thread($answerAdherent1);
        $this->setReference('thread', $thread);

        $manager->persist($thread);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadIdeaAnswerData::class,
        ];
    }
}

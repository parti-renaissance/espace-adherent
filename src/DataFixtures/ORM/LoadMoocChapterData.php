<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Mooc\Chapter;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMoocChapterData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $chapter1 = new Chapter(
            'Semaine 1 : Le coup de fourchette pour détendre notre santé',
            true,
            new \DateTime(date('Y-m-d', strtotime('+1 days')).' 09:30:00'),
            1
        );

        $chapter1->addVideo($this->getReference('mooc-video-1'));
        $chapter1->addVideo($this->getReference('mooc-video-2'));
        $this->addReference('mooc-chapter-1', $chapter1);

        $manager->persist($chapter1);

        $chapter2 = new Chapter(
            'Semaine 2 : Le coup de fourchette pour défendre la nature',
            true,
            new \DateTime(date('Y-m-d', strtotime('+5 days')).' 09:30:00'),
            2
        );
        $chapter2->addVideo($this->getReference('mooc-video-3'));
        $this->addReference('mooc-chapter-2', $chapter2);

        $manager->persist($chapter2);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadMoocVideoData::class,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Mooc\Chapter;
use App\Entity\Mooc\MoocQuizElement;
use App\Entity\Mooc\MoocVideoElement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadMoocChapterData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $chapter1 = new Chapter(
            'Semaine 1 : Le coup de fourchette pour détendre notre santé',
            true,
            new \DateTime(date('Y-m-d', strtotime('+1 days')).' 09:30:00')
        );

        $chapter1->addElement($this->getReference('mooc-video-1', MoocVideoElement::class));
        $chapter1->addElement($this->getReference('mooc-video-2', MoocVideoElement::class));
        $chapter1->addElement($this->getReference('mooc-quiz-1', MoocQuizElement::class));
        $this->addReference('mooc-chapter-1', $chapter1);

        $manager->persist($chapter1);

        $chapter2 = new Chapter(
            'Semaine 2 : Le coup de fourchette pour défendre la nature',
            false,
            new \DateTime(date('Y-m-d', strtotime('+5 days')).' 09:30:00')
        );
        $chapter2->addElement($this->getReference('mooc-video-3', MoocVideoElement::class));
        $this->addReference('mooc-chapter-2', $chapter2);

        $manager->persist($chapter2);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadMoocVideoData::class,
            LoadMoocQuizData::class,
        ];
    }
}

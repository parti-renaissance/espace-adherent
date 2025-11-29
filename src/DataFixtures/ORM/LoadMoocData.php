<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Image;
use App\Entity\Mooc\Chapter;
use App\Entity\Mooc\Mooc;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadMoocData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $mooc = new Mooc(
            'Faire de sa fourchette un acte politique',
            'Description du MOOC, faire de sa fourchette un acte politique',
            '<strong>Lorem ipsum</strong> dolor sit amet, consectetur adipiscing elit.',
            'ktHEfEDhscU',
            Chronos::createFromTime(0, 2, 10),
            'Bonsoir, voici un tweet de partage d\'un MOOC #enmarche',
            'Bonsoir, voici un partage avec Facebook',
            'Bonsoir, voici un email de partage !',
            'Voici le contenu de l\'email de partage. Merci.'
        );

        $mooc->addChapter($this->getReference('mooc-chapter-1', Chapter::class));
        $mooc->addChapter($this->getReference('mooc-chapter-2', Chapter::class));
        $manager->persist($mooc);

        $moocWithImage = new Mooc(
            'La Rentrée des Territoires',
            'Description du MOOC, la Rentrée des Territoires',
            '<strong>Lorem ipsum</strong> dolor sit amet, consectetur adipiscing elit.',
            'xha98D_Hoos',
            Chronos::createFromTime(0, 2, 10),
            'Bonsoir, voici un tweet de partage d\'un MOOC #enmarche',
            'Bonsoir, voici un partage avec Facebook',
            'Bonsoir, voici un email de partage !',
            'Voici le contenu de l\'email de partage. Merci.'
        );

        $image = new Image(Uuid::fromString('745a98fd-a55c-4168-bb26-a5db550b844c'));
        $image->setExtension('jpg');

        copy('app/data/static/proteger-la-france.jpg', 'app/data/images/'.$image->getUuid().'.jpg');

        $moocWithImage->setListImage($image);

        $manager->persist($moocWithImage);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadMoocChapterData::class,
        ];
    }
}

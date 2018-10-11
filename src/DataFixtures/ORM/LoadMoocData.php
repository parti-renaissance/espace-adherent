<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Mooc\Mooc;
use Cake\Chronos\MutableDateTime;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadMoocData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $mooc = new Mooc(
            'Faire de sa fourchette un acte politique',
            'Description du MOOC, faire de sa fourchette un acte politique',
            '<strong>Lorem ipsum</strong> dolor sit amet, consectetur adipiscing elit.',
            'ktHEfEDhscU',
            MutableDateTime::createFromTime(00, 02, 10),
            'Bonsoir, voici un tweet de partage d\'un MOOC #enmarche',
            'Bonsoir, voici un partage avec Facebook',
            'Bonsoir, voici un email de partage !',
            'Voici le contenu de l\'email de partage. Merci.'
        );

        $mooc->addChapter($this->getReference('mooc-chapter-1'));
        $mooc->addChapter($this->getReference('mooc-chapter-2'));
        $manager->persist($mooc);

        $moocWithImage = new Mooc(
            'La Rentrée des Territoires',
            'Description du MOOC, la Rentrée des Territoires',
            '<strong>Lorem ipsum</strong> dolor sit amet, consectetur adipiscing elit.',
            'xha98D_Hoos',
            MutableDateTime::createFromTime(00, 02, 10),
            'Bonsoir, voici un tweet de partage d\'un MOOC #enmarche',
            'Bonsoir, voici un partage avec Facebook',
            'Bonsoir, voici un email de partage !',
            'Voici le contenu de l\'email de partage. Merci.'
        );

        $moocWithImage->setImageName(new UploadedFile('src/DataFixtures/citizen-projects/default.png', 'default.png'));

        $manager->persist($moocWithImage);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadMoocChapterData::class,
        ];
    }
}

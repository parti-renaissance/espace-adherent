<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\Note;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadNoteData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $need = $this->getReference('need-legal');
        $scale = $this->getReference('scale-european');
        $theme = $this->getReference('theme-army-defense');
        $committee = $this->getReference('committee-1');
        $adherent = $this->getReference('adherent-3');

        $noteName = 'Faire la paix';
        $noteMakePeace = Note::create(
            Note::createUuid($noteName),
            $noteName,
            $adherent
        );
        $noteMakePeace->setPublishedAt(new \DateTime());
        $noteMakePeace->addNeed($need);
        $noteMakePeace->setScale($scale);
        $noteMakePeace->setTheme($theme);
        $noteMakePeace->setCommittee($committee);
        $this->addReference('note-peace', $noteMakePeace);

        $noteName = 'Favoriser l\'écologie';
        $noteHelpEcology = Note::create(
            Note::createUuid($noteName),
            $noteName,
            $adherent
        );
        $noteHelpEcology->setCommittee($committee);
        $this->addReference('note-help-ecology', $noteHelpEcology);

        $noteName = 'Aider les gens';
        $noteHelpPeople = Note::create(
            Note::createUuid($noteName),
            $noteName,
            $adherent
        );
        $noteHelpPeople->setPublishedAt(new \DateTime());
        $noteHelpPeople->setScale($scale);
        $noteHelpPeople->setCommittee($committee);
        $this->addReference('note-help-people', $noteHelpPeople);

        $manager->persist($noteMakePeace);
        $manager->persist($noteHelpEcology);
        $manager->persist($noteHelpPeople);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadNeedData::class,
            LoadScaleData::class,
            LoadThemeData::class,
            LoadAdherentData::class,
        ];
    }
}

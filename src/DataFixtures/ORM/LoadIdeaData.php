<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\Idea;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $need = $this->getReference('need-legal');
        $category = $this->getReference('category-european');
        $theme = $this->getReference('theme-army-defense');
        $committee = $this->getReference('committee-1');
        $adherent = $this->getReference('adherent-3');

        $ideaName = 'Faire la paix';
        $ideaMakePeace = new Idea(
            Idea::createUuid($ideaName),
            $ideaName,
            $adherent,
            $category,
            $theme,
            $committee,
            new \DateTime()
        );
        $ideaMakePeace->addNeed($need);
        $this->addReference('idea-peace', $ideaMakePeace);

        $ideaName = 'Favoriser l\'écologie';
        $ideaHelpEcology = new Idea(
            Idea::createUuid($ideaName),
            $ideaName,
            $adherent,
            $category,
            $theme,
            $committee
        );
        $this->addReference('idea-help-ecology', $ideaHelpEcology);

        $ideaName = 'Aider les gens';
        $ideaHelpPeople = new Idea(
            Idea::createUuid($ideaName),
            $ideaName,
            $adherent,
            $category,
            $theme,
            $committee,
            new \DateTime()
        );
        $this->addReference('idea-help-people', $ideaHelpPeople);

        $manager->persist($ideaMakePeace);
        $manager->persist($ideaHelpEcology);
        $manager->persist($ideaHelpPeople);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadIdeaNeedData::class,
            LoadIdeaCategoryData::class,
            LoadIdeaThemeData::class,
            LoadIdeaGuidelineData::class,
        ];
    }
}

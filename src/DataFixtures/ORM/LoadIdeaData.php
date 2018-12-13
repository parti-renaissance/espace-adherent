<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\AuthorCategoryEnum;
use AppBundle\Entity\IdeasWorkshop\Idea;
use AppBundle\Entity\IdeasWorkshop\IdeaStatusEnum;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadIdeaData extends AbstractFixture implements DependentFixtureInterface
{
    public const IDEA_01_UUID = 'e4ac3efc-b539-40ac-9417-b60df432bdc5';
    public const IDEA_02_UUID = '3b1ea810-115f-4b2c-944d-34a55d7b7e4d';
    public const IDEA_03_UUID = 'aa093ce6-8b20-4d86-bfbc-91a73fe47285';
    public const IDEA_04_UUID = 'c14937d6-fd42-465c-8419-ced37f3e6194';

    public function load(ObjectManager $manager)
    {
        $need = $this->getReference('need-legal');
        $category = $this->getReference('category-european');
        $theme = $this->getReference('theme-army-defense');
        $committee = $this->getReference('committee-1');
        $adherent3 = $this->getReference('adherent-3');
        $adherent6 = $this->getReference('adherent-6');

        $ideaMakePeace = new Idea(
            Uuid::fromString(self::IDEA_01_UUID),
            'Faire la paix',
            $adherent3,
            $category,
            $theme,
            AuthorCategoryEnum::COMMITTEE,
            true,
            $committee,
            new \DateTime('2018-12-01 10:00:00'),
            IdeaStatusEnum::PENDING
        );
        $ideaMakePeace->addNeed($need);
        $this->addReference('idea-peace', $ideaMakePeace);

        $ideaHelpEcology = new Idea(
            Uuid::fromString(self::IDEA_02_UUID),
            'Favoriser l\'écologie',
            $adherent3,
            $category,
            $theme,
            AuthorCategoryEnum::COMMITTEE,
            true,
            $committee,
            new \DateTime('2018-12-02 10:00:00')
        );
        $this->addReference('idea-help-ecology', $ideaHelpEcology);

        $ideaHelpPeople = new Idea(
            Uuid::fromString(self::IDEA_03_UUID),
            'Aider les gens',
            $adherent6,
            $category,
            $theme,
            AuthorCategoryEnum::QG,
            false,
            null,
            new \DateTime('2018-12-03 10:00:00')
        );
        $this->addReference('idea-help-people', $ideaHelpPeople);

        $ideaReduceWaste = new Idea(
            Uuid::fromString(self::IDEA_04_UUID),
            'Réduire le gaspillage',
            $adherent3,
            $category,
            $theme,
            AuthorCategoryEnum::ADHERENT,
            false,
            null,
            new \DateTime('2018-12-04 10:00:00'),
            IdeaStatusEnum::FINALIZED
        );
        $this->addReference('idea-reduce-waste', $ideaReduceWaste);

        $manager->persist($ideaMakePeace);
        $manager->persist($ideaHelpEcology);
        $manager->persist($ideaHelpPeople);
        $manager->persist($ideaReduceWaste);

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

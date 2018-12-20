<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\DataFixtures\AutoIncrementResetter;
use AppBundle\Entity\IdeasWorkshop\Theme;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadIdeaThemeData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        AutoIncrementResetter::resetAutoIncrement($manager, 'ideas_workshop_theme');

        $themeArmyDefense = new Theme(
            'Armées et défense',
            'default.png',
            true
        );
        $this->addReference('theme-army-defense', $themeArmyDefense);

        $themeTreasure = new Theme(
            'Trésorerie',
            null,
            true
        );
        $this->addReference('theme-treasure', $themeTreasure);

        $themeNotPublished = new Theme(
            'Thème non publié'
        );
        $this->addReference('theme-not-published', $themeNotPublished);

        $manager->persist($themeArmyDefense);
        $manager->persist($themeTreasure);
        $manager->persist($themeNotPublished);

        $manager->flush();
    }
}

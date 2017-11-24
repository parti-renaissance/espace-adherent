<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\CitizenProjectCategorySkill;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCitizenProjectCategorySkillData extends AbstractFixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $category1 = $this->getReference('cpc001');

        $skill1 = $this->getReference('cps001');
        $skill2 = $this->getReference('cps002');
        $skill3 = $this->getReference('cps003');

        $category1Skill1 = new CitizenProjectCategorySkill($category1, $skill1, true);
        $category1Skill2 = new CitizenProjectCategorySkill($category1, $skill2, true);
        $category1Skill3 = new CitizenProjectCategorySkill($category1, $skill3, false);

        $manager->persist($category1Skill1);
        $manager->persist($category1Skill2);
        $manager->persist($category1Skill3);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadCitizenProjectSkillData::class,
            LoadCitizenProjectCategoryData::class,
        ];
    }
}

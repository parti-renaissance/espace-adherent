<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\MoocEventCategory;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadMoocEventCategoryData extends AbstractFixture implements FixtureInterface
{
    const MOOC_EVENT_CATEGORIES = [
        'MEC001' => 'Séance MOOC n° 1',
        'MEC002' => 'Séance MOOC n° 2',
        'MEC003' => 'Séance MOOC n° 3',
        'MEC004' => 'Séance MOOC n° 4',
        'MEC005' => 'Séance MOOC n° 5',
    ];

    public function load(ObjectManager $manager)
    {
        $position = 1;
        foreach (self::MOOC_EVENT_CATEGORIES as $name) {
            $category = new MoocEventCategory($name);

            $this->addReference('mooc-event-category-'.$position, $category);

            $manager->persist($category);
            ++$position;
        }

        $manager->flush();
    }
}

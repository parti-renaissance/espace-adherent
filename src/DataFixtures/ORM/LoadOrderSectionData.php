<?php

namespace App\DataFixtures\ORM;

use App\Entity\OrderSection;
use Doctrine\Persistence\ObjectManager;

class LoadOrderSectionData extends AbstractFixtures
{
    public const ORDER_SECTION = [
        'OS001' => 'Articles',
        'OS002' => 'Tribune',
        'OS003' => 'Lexique',
        'OS004' => 'Autre resources',
    ];

    public function load(ObjectManager $manager)
    {
        $position = 0;
        foreach (self::ORDER_SECTION as $key => $name) {
            $section = new OrderSection(++$position, $name);
            $manager->persist($section);
            $this->addReference(strtolower($key), $section);
        }

        $manager->flush();
    }
}

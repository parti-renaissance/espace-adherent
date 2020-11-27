<?php

namespace App\DataFixtures\ORM;

use App\Entity\Jecoute\News;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadJecouteNewsData extends Fixture
{
    public const NEWS_1_UUID = '16373659-fed1-443c-a956-a257e2c2bae4';
    public const NEWS_2_UUID = '0bc3f920-da90-4773-80e1-a388005926fc';

    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createNews(
            self::NEWS_1_UUID,
            'Nouveau sondage disponible',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.',
        ));

        $manager->persist($this->createNews(
            self::NEWS_2_UUID,
            'Rassemblement',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.',
        ));

        $manager->flush();
    }

    private function createNews(string $uuid, string $title, string $text): News
    {
        return new News(
            Uuid::fromString($uuid),
            $title,
            $text
        );
    }
}

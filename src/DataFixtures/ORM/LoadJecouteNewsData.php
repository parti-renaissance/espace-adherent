<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\News;
use App\Jecoute\JecouteSpaceEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadJecouteNewsData extends Fixture implements DependentFixtureInterface
{
    public const NEWS_1_UUID = '16373659-fed1-443c-a956-a257e2c2bae4';
    public const NEWS_2_UUID = '0bc3f920-da90-4773-80e1-a388005926fc';
    public const NEWS_3_UUID = '232f99b8-7a0c-40ed-ba9e-bf8f33e19052';
    public const NEWS_4_UUID = 'b2b8e6a3-f5a9-4b34-a761-37438c3c3602';
    public const NEWS_5_UUID = '6c70f8e8-6bce-4376-8b9e-3ce342880673';
    public const NEWS_6_UUID = '560bab7a-d624-47d6-bf5e-3864c2406daf';

    public function load(ObjectManager $manager)
    {
        $manager->persist($this->createNews(
            self::NEWS_1_UUID,
            'Nouveau sondage disponible',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.',
            'global_topic',
            null,
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_59'),
            JecouteSpaceEnum::CANDIDATE_SPACE
        ));

        $manager->persist($this->createNews(
            self::NEWS_2_UUID,
            'Rassemblement',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.',
            'global_topic',
            'https://en-marche.fr',
            LoadGeoZoneData::getZoneReference($manager, 'zone_region_11'),
            JecouteSpaceEnum::CANDIDATE_SPACE
        ));

        $manager->persist($this->createNews(
            self::NEWS_3_UUID,
            'Nouveau assemblement',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.',
            null,
            'https://en-marche.fr',
            null,
            null,
            true,
            false
        ));

        $manager->persist($this->createNews(
            self::NEWS_4_UUID,
            'Nouvelle actualité à 92 du référent',
            'Ut porttitor vitae velit sit amet posuere. Mauris semper sagittis diam, convallis viverra lorem rutrum.',
            null,
            'https://referent.en-marche.fr',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'),
            JecouteSpaceEnum::REFERENT_SPACE,
            true,
            true,
            $this->getReference('adherent-8')
        ));

        $manager->persist($this->createNews(
            self::NEWS_5_UUID,
            'Nouvelle actualité non publié à 92 du référent délégué',
            'Fusce lacinia, diam et sodales iaculis, velit ante mollis ex, eu commodo felis lectus eu dui.',
            null,
            'https://referent.en-marche.fr',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'),
            JecouteSpaceEnum::REFERENT_SPACE,
            true,
            false,
            $this->getReference('senator-59')
        ));

        $manager->persist($this->createNews(
            self::NEWS_6_UUID,
            'Nouvelle actualité à 92 de l\'admin',
            'Curabitur in fermentum urna, sit amet venenatis orci. Proin accumsan ultricies congue.',
            null,
            'https://referent.en-marche.fr',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92')
        ));

        $manager->flush();
    }

    private function createNews(
        string $uuid,
        string $title,
        string $text,
        string $topic = null,
        string $externalLink = null,
        Zone $zone = null,
        string $space = null,
        bool $notification = true,
        bool $published = true,
        Adherent $author = null
    ): News {
        $news = new News(
            Uuid::fromString($uuid),
            $title,
            $text,
            $topic,
            $externalLink,
            $zone,
            $notification,
            $published
        );
        $news->setSpace($space);
        if ($author) {
            $news->setAuthor($author);
        }

        return $news;
    }

    public function getDependencies()
    {
        return [
            LoadGeoZoneData::class,
            LoadAdherentData::class,
        ];
    }
}

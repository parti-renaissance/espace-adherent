<?php

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\News;
use App\Scope\ScopeEnum;
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
    public const NEWS_7_UUID = '25632c43-c224-4745-84d7-09dfa8249367';
    public const NEWS_8_UUID = '72b68bf7-de51-4325-8933-02d2ff658dc3';
    public const NEWS_9_UUID = '82068546-47d1-4f78-a6ba-692812984442';
    public const NEWS_10_UUID = 'dd938794-2b00-400c-a817-9e04b5d20bc0';
    public const NEWS_11_UUID = 'b09185ba-f271-404b-a73f-76d92ca8c120';
    public const NEWS_12_UUID = '6101c6a6-f7ef-4952-95db-8553952d656d';
    public const NEWS_13_UUID = '2c28b246-b17e-409d-992a-b8a57481fb7a';
    public const NEWS_14_UUID = '4f5db386-1819-4055-abbd-fb5d840cd6c0';

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createNews(
            self::NEWS_1_UUID,
            'Nouveau sondage disponible',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.',
            null,
            null,
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_59'),
        ));

        $manager->persist($this->createNews(
            self::NEWS_2_UUID,
            'Rassemblement',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.',
            'https://en-marche.fr',
            'Voir',
            LoadGeoZoneData::getZoneReference($manager, 'zone_region_11'),
            true,
            true,
            null,
            new \DateTime('-1 hour')
        ));

        $manager->persist($object = $this->createNews(
            self::NEWS_3_UUID,
            'Nouveau assemblement',
            'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec a commodo diam. Etiam congue auctor dui, non consequat libero faucibus sit amet.',
            'https://en-marche.fr',
            'Voir',
            null,
            true,
            false,
            null,
            new \DateTime('-2 hours')
        ));
        $object->setAuthorInstance(ScopeEnum::SCOPE_INSTANCES[ScopeEnum::NATIONAL]);

        $manager->persist($this->createNews(
            self::NEWS_4_UUID,
            'Nouvelle actualité à 92 du référent',
            'Ut porttitor vitae velit sit amet posuere. Mauris semper sagittis diam, convallis viverra lorem rutrum.',
            'https://referent.en-marche.fr',
            'Voir',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'),
            true,
            true,
            $this->getReference('adherent-8', Adherent::class),
            new \DateTime('-3 hours')
        ));

        $manager->persist($this->createNews(
            self::NEWS_5_UUID,
            'Nouvelle actualité non publiée à 59 du référent délégué',
            'Fusce lacinia, diam et sodales iaculis, velit ante mollis ex, eu commodo felis lectus eu dui.',
            'https://referent.en-marche.fr',
            'Voir le lien',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_59'),
            true,
            false,
            $this->getReference('senator-59', Adherent::class),
            new \DateTime('-4 hours')
        ));

        $manager->persist($this->createNews(
            self::NEWS_6_UUID,
            'Nouvelle actualité à 92 de l\'admin',
            'Curabitur in fermentum urna, sit amet venenatis orci. Proin accumsan ultricies congue.',
            'https://referent.en-marche.fr',
            'Voir',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'),
            true,
            true,
            null,
            new \DateTime('-5 hours')
        ));

        $manager->persist($this->createNews(
            self::NEWS_7_UUID,
            'Une actualité à 75',
            'Quisque interdum lectus et ultrices rhoncus. Cras nunc diam, rutrum eget velit vel, cursus varius justo.',
            'https://75.en-marche.fr',
            'Voir le lien',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_75'),
            true,
            true,
            $this->getReference('adherent-19', Adherent::class),
            new \DateTime('-6 hours')
        ));

        $manager->persist($this->createNews(
            self::NEWS_8_UUID,
            'Actualité épinglée à 92 du référent',
            'Nulla facilisi. Vestibulum vitae neque justo. Aliquam fringilla accumsan metus, sit amet blandit ligula.',
            'https://epingle.en-marche.fr',
            'Voir le lien',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'),
            true,
            true,
            $this->getReference('adherent-8', Adherent::class),
            new \DateTime('-2 days')
        ));

        $manager->persist($this->createNews(
            self::NEWS_9_UUID,
            'Actualité épinglée et enrichie à 92 du référent',
            '**Tincidunt** Sed vitae erat sagittis, *ultricies* nulla et, tincidunt eros.
# In hac habitasse platea dictumst
## Pellentesque imperdiet erat arcu
Cras hendrerit, mi et malesuada convallis, elit orci hendrerit purus, a euismod erat nisl at lorem.

### Eget varius felis sodales sit amet

Nulla at odio non augue congue sollicitudin.  [Mon URL](https://en-marche.fr)
Nulla ac augue sapien. In tincidunt nec massa ac rhoncus.![Mon image](https://cdn.futura-sciences.com/buildsv6/images/mediumoriginal/6/5/2/652a7adb1b_98148_01-intro-773.jpg)

Cras vitae fringilla nunc. Suspendisse facilisis rhoncus urna a placerat.

* Vestibulum facilisis convallis mauris eu eleifend.
* Aenean sit amet elementum ex.
* In erat enim, pulvinar quis dui et, volutpat imperdiet nulla.

Sed eu nibh tempor, pulvinar lectus ac, mattis nunc.

1. Praesent scelerisque sagittis orci in sagittis.
2. Phasellus iaculis elementum iaculis.

Nulla facilisi. Vestibulum vitae neque justo. Aliquam fringilla accumsan metus, sit amet blandit ligula.',
            'https://epingle.en-marche.fr',
            'Voir le lien',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'),
            true,
            true,
            $this->getReference('adherent-8', Adherent::class),
            new \DateTime('-3 days')
        ));

        $manager->persist($object = $this->createNews(
            self::NEWS_10_UUID,
            'Pour toute la France',
            'Nulla eleifend sed nisl eget efficitur. Nunc at ante diam. Phasellus condimentum dui nisi, sed imperdiet elit porttitor ut. Sed bibendum congue hendrerit. Proin pretium augue a urna interdum, ac congue felis egestas.',
            'https://en-marche.fr',
            'Voir le lien',
            null,
            false,
            true,
            null,
            new \DateTime('-1 day')
        ));
        $object->setAuthorInstance(ScopeEnum::SCOPE_INSTANCES[ScopeEnum::NATIONAL]);

        $manager->persist($this->createNews(
            self::NEWS_11_UUID,
            'Une actualité du correspondent à 92',
            'Cras libero mauris, euismod blandit ornare eu, congue quis nulla. Maecenas sodales diam nec tincidunt pulvinar.',
            'https://92.en-marche.fr',
            'Voir le lien',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'),
            false,
            true,
            $this->getReference('correspondent-1', Adherent::class),
            new \DateTime('-7 hours')
        ));

        $manager->persist($this->createNews(
            self::NEWS_12_UUID,
            'Une actualité à 75',
            'Ut at porttitor ipsum. Sed quis volutpat ipsum.',
            'https://92-delegated.en-marche.fr',
            'Voir le lien',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'),
            false,
            true,
            $this->getReference('adherent-9', Adherent::class),
            new \DateTime('-8 hours')
        ));

        $manager->persist($this->createNews(
            self::NEWS_13_UUID,
            'Une actualité d\'un candidat aux législatives à 75-1',
            'Donec viverra odio.',
            'https://un-candidat-aux-legislatives.en-marche.fr',
            'Voir le lien',
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'),
            false,
            true,
            $this->getReference('senatorial-candidate', Adherent::class),
            new \DateTime('-10 hours')
        ));

        $manager->persist($this->createNews(
            self::NEWS_14_UUID,
            'Une actualité d\'un candidat aux législatives délégué à 75-1',
            'Aenean varius condimentum diam in rutrum.',
            'https://un-candidat-aux-legislatives-delegue.en-marche.fr',
            'Voir le lien',
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'),
            false,
            true,
            $this->getReference('adherent-5', Adherent::class),
            new \DateTime('-9 hours')
        ));

        $manager->flush();
    }

    private function createNews(
        string $uuid,
        string $title,
        string $text,
        ?string $externalLink = null,
        ?string $linkLabel = null,
        ?Zone $zone = null,
        bool $notification = true,
        bool $published = true,
        ?Adherent $author = null,
        ?\DateTime $createdAt = null,
    ): News {
        $news = new News(
            Uuid::fromString($uuid),
            $title,
            $text,
            $externalLink,
            $linkLabel,
            $zone,
            $notification,
            $published,
        );
        $news->setCreatedAt($createdAt ?? new \DateTime());
        if ($author) {
            $news->setAuthor($author);
        }

        return $news;
    }

    public function getDependencies(): array
    {
        return [
            LoadGeoZoneData::class,
            LoadAdherentData::class,
        ];
    }
}

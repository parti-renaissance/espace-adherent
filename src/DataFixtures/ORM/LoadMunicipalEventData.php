<?php

namespace App\DataFixtures\ORM;

use App\Entity\Event\MunicipalEvent;
use App\Entity\PostAddress;
use App\FranceCities\FranceCities;
use Cake\Chronos\Chronos;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadMunicipalEventData extends Fixture implements DependentFixtureInterface
{
    private FranceCities $franceCities;

    public function __construct(FranceCities $franceCities)
    {
        $this->franceCities = $franceCities;
    }

    public function load(ObjectManager $manager)
    {
        $eventCategory1 = $this->getReference('CE001');

        $event = new MunicipalEvent(
            Uuid::uuid4(),
            $this->getReference('municipal-chief-1'),
            null,
            'Event municipal',
            $eventCategory1,
            'Allons à la rencontre des citoyens.',
            $this->createPostAddress('60 avenue des Champs-Élysées', '75008-75108', null, 48.870507, 2.313243),
            (new Chronos('+3 days'))->format('Y-m-d').' 08:30:00',
            (new Chronos('+3 days'))->format('Y-m-d').' 19:00:00'
        );

        $manager->persist($event);

        $manager->flush();
    }

    private function createPostAddress(
        string $street,
        string $cityCode,
        string $region = null,
        float $latitude = null,
        float $longitude = null
    ): PostAddress {
        [$postalCode, $inseeCode] = explode('-', $cityCode);
        $city = $this->franceCities->getCityByInseeCode($inseeCode);

        return PostAddress::createFrenchAddress($street, $cityCode, $city ? $city->getName() : null, $region, $latitude, $longitude);
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadEventCategoryData::class,
        ];
    }
}

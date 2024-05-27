<?php

namespace App\DataFixtures\ORM;

use App\Action\ActionTypeEnum;
use App\Entity\Action\Action;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Tests\App\Test\Geocoder\DummyGeocoder;

class LoadActionData extends AbstractLoadPostAddressData implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $coordinates = array_values(DummyGeocoder::$coordinates);

        $types = array_values(ActionTypeEnum::toArray());

        $adherents = [
            $this->getReference('adherent-1'),
            $this->getReference('adherent-2'),
        ];

        for ($i = 1; $i <= 50; ++$i) {
            $action = new Action();
            $action->type = $types[$i % \count($types)];
            $coordinate = $coordinates[array_rand($coordinates)];
            $action->setPostAddress($this->createPostAddress('68 rue du Rocher', '75008-75108', latitude: $coordinate['latitude'], longitude: $coordinate['longitude']));
            $action->setAuthor(0 === $i % 2 ? $adherents[0] : $adherents[1]);
            $action->setZones([LoadGeoZoneData::getZoneReference($manager, 'zone_department_92')]);
            $action->date = new \DateTime('+'.$i.' hours');
            $action->description = '<p>description</p>';

            $manager->persist($action);
            $this->addReference('action-'.$i, $action);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}

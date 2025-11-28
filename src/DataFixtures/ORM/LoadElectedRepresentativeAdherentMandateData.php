<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\ElectedRepresentative\MandateTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadElectedRepresentativeAdherentMandateData extends Fixture implements DependentFixtureInterface
{
    private const MANDATE_UUID_01 = 'c1464d05-0a25-4ca9-95c0-0d0004644986';
    private const MANDATE_UUID_02 = 'a31bfe33-9d13-4b65-ad6c-653e75c6adb9';
    private const MANDATE_UUID_03 = 'd91df367-14df-474d-ac9a-8e2176657f71';

    public function load(ObjectManager $manager): void
    {
        /** @var Adherent $gisele */
        $gisele = $this->getReference('adherent-5', Adherent::class);
        /** @var Adherent $erDepartment92 */
        $erDepartment92 = $this->getReference('renaissance-user-2', Adherent::class);

        $manager->persist(ElectedRepresentativeAdherentMandate::create(
            Uuid::fromString(self::MANDATE_UUID_01),
            $gisele,
            MandateTypeEnum::CITY_COUNCIL,
            new \DateTime('2019-07-23'),
            new \DateTime('2023-06-11'),
            'Conseiller(e) municipal(e)',
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_community_200054781')
        ));

        $manager->persist(ElectedRepresentativeAdherentMandate::create(
            Uuid::fromString(self::MANDATE_UUID_02),
            $gisele,
            MandateTypeEnum::CITY_COUNCIL,
            new \DateTime('2019-06-12'),
            null,
            'Conseiller(e) municipal(e)',
            LoadGeoZoneData::getZoneReference($manager, 'zone_city_community_200054781')
        ));

        $manager->persist(ElectedRepresentativeAdherentMandate::create(
            Uuid::fromString(self::MANDATE_UUID_03),
            $erDepartment92,
            MandateTypeEnum::SENATOR,
            new \DateTime('2019-01-11'),
            null,
            'Sénatrice',
            LoadGeoZoneData::getZoneReference($manager, 'zone_department_92')
        ));

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}

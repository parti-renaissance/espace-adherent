<?php

namespace App\DataFixtures\ORM;

use App\Entity\District;
use App\Entity\ReferentTag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadDistrictData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $districtRepository = $manager->getRepository(District::class);

        // Create referent tags for districts
        foreach ($districtRepository->findAll() as $district) {
            $tag = new ReferentTag($district->getFullName(), 'CIRCO_'.$district->getCode());
            $tag->setType(ReferentTag::TYPE_DISTRICT);
            $district->setReferentTag($tag);
            $manager->persist($tag);
            $this->setReference("referent_tag_circo_{$district->getCode()}", $tag);
        }

        $manager->flush();

        // Initialize district referent tags for adherents
        $sql = <<<SQL
INSERT INTO adherent_referent_tag (adherent_id, referent_tag_id)
(
   SELECT adherent.id, tag.id
   FROM adherents adherent
   INNER JOIN referent_tags tag
   INNER JOIN districts district ON district.referent_tag_id = tag.id
   INNER JOIN geo_data ON geo_data.id = district.geo_data_id
   WHERE ST_Within(ST_GeomFromText(CONCAT('POINT (', adherent.address_longitude, ' ', adherent.address_latitude, ')')), geo_data.geo_shape) = 1
);
SQL;

        $manager->getConnection()->exec($sql);
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadGeoZoneData::class,
        ];
    }
}

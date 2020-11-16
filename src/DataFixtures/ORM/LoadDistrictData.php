<?php

namespace App\DataFixtures\ORM;

use App\Deputy\LightFileDistrictLoader;
use App\Entity\District;
use App\Entity\ReferentTag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadDistrictData extends Fixture implements DependentFixtureInterface
{
    private $districtLoader;

    public function __construct(LightFileDistrictLoader $districtLoader)
    {
        $this->districtLoader = $districtLoader;
    }

    public function load(ObjectManager $manager)
    {
        $this->districtLoader->load(
            __DIR__.'/../deputy/circonscriptions_all.csv',
            __DIR__.'/../deputy/france_circonscriptions_legislatives.json',
            __DIR__.'/../deputy/country_boundaries.json'
        );

        $districtRepository = $manager->getRepository(District::class);

        $this->getReference('deputy-75-1')->setManagedDistrict($districtRepository->findOneBy(['code' => '75001']));
        $this->getReference('deputy-75-2')->setManagedDistrict($districtRepository->findOneBy(['code' => '75002']));
        $this->getReference('senatorial-candidate')->setLegislativeCandidateManagedDistrict($districtRepository->findOneBy(['code' => '75002']));

        $this->getReference('deputy-ch-li')->setManagedDistrict($districtRepository->findOneBy(['code' => 'FDE-06']));

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
        ];
    }
}

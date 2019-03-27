<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Deputy\LightFileDistrictLoader;
use AppBundle\Entity\District;
use AppBundle\Entity\ReferentTag;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class LoadDistrictData extends AbstractFixture implements FixtureInterface, ContainerAwareInterface, DependentFixtureInterface
{
    use ContainerAwareTrait;

    private $districtLoader;

    public function load(ObjectManager $manager)
    {
        $this->districtLoader = $this->container->get(LightFileDistrictLoader::class);

        $this->districtLoader->load(
            __DIR__.'/../deputy/circonscriptions_all.csv',
            __DIR__.'/../deputy/france_circonscriptions_legislatives.json',
            __DIR__.'/../deputy/country_boundaries.json'
        );

        $districtRepository = $manager->getRepository(District::class);

        $deputy_75_1 = $this->getReference('deputy-75-1');
        $district_75_1 = $districtRepository->findOneBy(['code' => '75001']);
        $district_75_1->setAdherent($deputy_75_1);

        $deputy_ch_li = $this->getReference('deputy-ch-li');
        $district_ch_li = $districtRepository->findOneBy(['code' => 'FDE-06']);
        $district_ch_li->setAdherent($deputy_ch_li);

        // Create referent tags for districts
        foreach ($districtRepository->findAll() as $district) {
            $tag = new ReferentTag($district->getFullName(), 'CIRCO_'.$district->getCode(), ReferentTag::CATEGORY_CIRCO);
            $district->setReferentTag($tag);
            $manager->persist($tag);
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

        $this->container->get('doctrine')->getManager()->getConnection()->exec($sql);
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}

<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Deputy\LightFileDistrictLoader;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\District;
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

        /** @var Adherent $deputy */
        $deputy = $this->getReference('deputy-75-1');
        $district_75_1 = $manager->getRepository(District::class)->findOneBy(['code' => '75001']);
        $district_75_1->setAdherent($deputy);
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}

<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\District;
use AppBundle\Entity\ManagedArea\DeputyManagedArea;
use AppBundle\Entity\ManagedArea\ElectedOfficerManagedArea;
use AppBundle\Entity\ManagedArea\ReferentManagedArea;
use AppBundle\Repository\DistrictRepository;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadManagedAreaData extends AbstractFixture implements DependentFixtureInterface
{
    private const MANAGED_AREAS = [
        'adherent-8' => [
            ReferentManagedArea::class => [
                'referent_tag_ch',
                'referent_tag_es',
                'referent_tag_92',
                'referent_tag_76',
                'referent_tag_77',
                'referent_tag_13',
            ],
            ElectedOfficerManagedArea::class => [
                'referent_tag_77',
                'referent_tag_13',
            ],
        ],
        'deputy-75-1' => [
            DeputyManagedArea::class => '75001',
        ],
    ];

    public function load(ObjectManager $manager)
    {
        /** @var DistrictRepository $districtRepository */
        $districtRepository = $manager->getRepository(District::class);

        foreach (self::MANAGED_AREAS as $adherent => $roles) {
            $adherent = $this->getReference($adherent);

            foreach ($roles as $role => $managedAreas) {
                switch($role) {
                    case DeputyManagedArea::class:
                        $district = $districtRepository->findOneBy(['code' => $managedAreas]);

                        $manager->persist(new DeputyManagedArea($adherent, $district));
                        break;
                    case ElectedOfficerManagedArea::class:
                        foreach ($managedAreas as $referentTag) {
                            $manager->persist(new ElectedOfficerManagedArea($adherent, $this->getReference($referentTag)));
                        }
                        break;
                    case ReferentManagedArea::class:
                        foreach ($managedAreas as $referentTag) {
                            $manager->persist(new ReferentManagedArea($adherent, $this->getReference($referentTag)));
                        }
                        break;
                }
            }
        }


        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadDistrictData::class,
            LoadReferentTagData::class,
        ];
    }
}

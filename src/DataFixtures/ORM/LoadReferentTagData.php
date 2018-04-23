<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\ReferentTag;
use AppBundle\Intl\UnitedNationsBundle;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadReferentTagData extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // French department tags
        foreach (\range(1, 98) as $department) {
            $department = \str_pad($department, 2, '0', STR_PAD_LEFT);

            switch ($department) {
                // 2 separate tags for Corsica
                case '20':
                    $this->createReferentTag($manager, 'Département 2A', '2a');
                    $this->createReferentTag($manager, 'Département 2B', '2b');

                    break;
                // 1 tag for each Paris district
                case '75':
                    foreach (\range(1, 20) as $district) {
                        $district = \str_pad($district, 2, '0', STR_PAD_LEFT);

                        $this->createReferentTag($manager, "750$district", "750$district");
                    }

                    break;
                // does not exist
                case '96':
                    break;
                default:
                    $this->createReferentTag($manager, "Département $department", $department);

                    break;
            }
        }

        foreach (UnitedNationsBundle::getCountries() as $countryCode => $countryName) {
            $this->createReferentTag($manager, $countryName, $countryCode);
        }

        $manager->flush();
    }

    private function createReferentTag(ObjectManager $manager, string $name, string $code): void
    {
        $referentTag = new ReferentTag($name, $code);

        $manager->persist($referentTag);

        $this->addReference('referent_tag_'.\mb_strtolower($code), $referentTag);
    }
}

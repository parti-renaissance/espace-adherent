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
            $department = \str_pad($department, 2, '0', \STR_PAD_LEFT);

            switch ($department) {
                // 2 separate tags for Corsica + 1 tag for whole Corsica
                case '20':
                    $this->createReferentTag($manager, 'Département 2A', '2A', ReferentTag::CATEGORY_DEPARTMENT);
                    $this->createReferentTag($manager, 'Département 2B', '2B', ReferentTag::CATEGORY_DEPARTMENT);
                    $this->createReferentTag($manager, 'Corse', '20', ReferentTag::CATEGORY_DEPARTMENT);

                    break;
                // 1 tag for each Paris district + 1 tag for Paris
                case '75':
                    foreach (\range(1, 20) as $district) {
                        $district = \str_pad($district, 2, '0', \STR_PAD_LEFT);

                        $this->createReferentTag($manager, "750$district", "750$district", ReferentTag::CATEGORY_ARRONDISSEMENT);
                    }

                    $this->createReferentTag($manager, 'Paris', '75', ReferentTag::CATEGORY_DEPARTMENT);

                    break;
                // does not exist
                case '96':
                    break;
                default:
                    $this->createReferentTag($manager, "Département $department", $department, ReferentTag::CATEGORY_DEPARTMENT);

                    break;
            }
        }

        foreach (UnitedNationsBundle::getCountries() as $countryCode => $countryName) {
            $this->createReferentTag($manager, $countryName, $countryCode, ReferentTag::CATEGORY_MONDE);
        }

        $manager->flush();
    }

    private function createReferentTag(ObjectManager $manager, string $name, string $code, string $category): void
    {
        $referentTag = new ReferentTag($name, $code, $category);

        $manager->persist($referentTag);

        $this->addReference('referent_tag_'.\mb_strtolower($code), $referentTag);
    }
}

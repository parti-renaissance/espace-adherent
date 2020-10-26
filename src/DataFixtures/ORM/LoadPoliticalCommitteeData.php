<?php

namespace App\DataFixtures\ORM;

use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadPoliticalCommitteeData extends Fixture
{
    private const ACTIVE_DEPARTMENTS = [
        '75', // Paris
        '77', // Seine-et-Marne
    ];

    public function load(ObjectManager $manager): void
    {
        $terCos = $manager->getRepository(TerritorialCouncil::class)->findAll();
        foreach ($terCos as $terCo) {
            $coPol = new PoliticalCommittee($terCo->getNameCodes(), $terCo);
            $coPol->setIsActive($this->determineShouldBeActive($coPol));

            $manager->persist($coPol);
            $this->addReference('coPol_'.$terCo->getCodes(), $coPol);
        }

        $manager->flush();
    }

    private function determineShouldBeActive(PoliticalCommittee $coPol): bool
    {
        foreach ($coPol->getTerritorialCouncil()->getReferentTags() as $referentTag) {
            if ($referentTag->isDepartmentTag() && \in_array($referentTag->getCode(), self::ACTIVE_DEPARTMENTS, true)) {
                return true;
            }
        }

        return false;
    }

    public function getDependencies(): array
    {
        return [
            LoadTerritorialCouncilData::class,
        ];
    }
}

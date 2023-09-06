<?php

namespace App\DataFixtures\ORM;

use App\Adherent\MandateTypeEnum;
use App\Entity\Adherent;
use App\Entity\Reporting\DeclaredMandateHistory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadDeclaredMandateHistoryData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $adherent5 = $this->getReference('adherent-5');
        $adherent13 = $this->getReference('adherent-13');
        $renaissanceUser2 = $this->getReference('renaissance-user-2');

        $manager->persist($this->createHistory(
            $adherent5,
            [MandateTypeEnum::CONSEILLER_MUNICIPAL],
            [MandateTypeEnum::MAIRE]
        ));

        $manager->persist($this->createHistory(
            $adherent13,
            [MandateTypeEnum::DEPUTE_EUROPEEN],
            []
        ));

        $manager->persist($this->createHistory(
            $renaissanceUser2,
            [MandateTypeEnum::DEPUTE_EUROPEEN],
            []
        ));

        $manager->flush();
    }

    public function createHistory(
        Adherent $adherent,
        array $addedMandates,
        array $removedMandates
    ): DeclaredMandateHistory {
        return new DeclaredMandateHistory($adherent, $addedMandates, $removedMandates);
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
        ];
    }
}

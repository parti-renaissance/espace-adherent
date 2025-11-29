<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Adherent\MandateTypeEnum;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Entity\Reporting\DeclaredMandateHistory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadDeclaredMandateHistoryData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $adherent5 = $this->getReference('adherent-5', Adherent::class);
        $adherent13 = $this->getReference('adherent-13', Adherent::class);
        $renaissanceUser2 = $this->getReference('renaissance-user-2', Adherent::class);
        $admin = $this->getReference('administrator-2', Administrator::class);

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

        $manager->persist($this->createHistory(
            $renaissanceUser2,
            [MandateTypeEnum::CONSEILLER_MUNICIPAL],
            [],
            $admin
        ));

        $manager->flush();
    }

    public function createHistory(
        Adherent $adherent,
        array $addedMandates,
        array $removedMandates,
        ?Administrator $administrator = null,
    ): DeclaredMandateHistory {
        $history = new DeclaredMandateHistory($adherent, $addedMandates, $removedMandates);

        if ($administrator) {
            $history->setAdministrator($administrator);
        }

        return $history;
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Report\CommitteeReport;
use App\Entity\Report\ReportReasonEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadReportData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $manager->persist(new CommitteeReport(
            $this->getReference('committee-1', Committee::class),
            $this->getReference('adherent-1', Adherent::class),
            [ReportReasonEnum::REASON_ILLICIT_CONTENT],
            null
        ));

        $manager->persist($report = new CommitteeReport(
            $this->getReference('committee-1', Committee::class),
            $this->getReference('adherent-2', Adherent::class),
            [ReportReasonEnum::REASON_OTHER, ReportReasonEnum::REASON_COMMERCIAL_CONTENT],
            'Ce comité n\'est pas intéressant.'
        ));
        $report->resolve();

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadAdherentData::class,
            LoadCommitteeV1Data::class,
        ];
    }
}

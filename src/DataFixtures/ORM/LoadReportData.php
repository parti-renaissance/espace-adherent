<?php

namespace App\DataFixtures\ORM;

use App\Entity\Report\CommitteeReport;
use App\Entity\Report\ReportReasonEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadReportData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $manager->persist(new CommitteeReport(
            $this->getReference('committee-1'),
            $this->getReference('adherent-1'),
            [ReportReasonEnum::REASON_ILLICIT_CONTENT],
            null
        ));

        $manager->persist($report = new CommitteeReport(
            $this->getReference('committee-1'),
            $this->getReference('adherent-2'),
            [ReportReasonEnum::REASON_OTHER, ReportReasonEnum::REASON_COMMERCIAL_CONTENT],
            'Ce comité n\'est pas intéressant.'
        ));
        $report->resolve();

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadCommitteeData::class,
        ];
    }
}

<?php

namespace App\DataFixtures\ORM;

use App\Entity\Report\CitizenProjectReport;
use App\Entity\Report\ReportReasonEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadReportData extends Fixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $report1 = new CitizenProjectReport(
            $this->getReference('citizen-project-1'),
            $this->getReference('adherent-1'),
            [ReportReasonEnum::REASON_ILLICIT_CONTENT],
            null
        );

        $report2 = new CitizenProjectReport(
            $this->getReference('citizen-project-1'),
            $this->getReference('adherent-2'),
            [ReportReasonEnum::REASON_OTHER, ReportReasonEnum::REASON_COMMERCIAL_CONTENT],
            'Ce projet n\'est pas intéressant.'
        );
        $report2->resolve();

        $report3 = new CitizenProjectReport(
            $this->getReference('citizen-project-1'),
            $this->getReference('adherent-3'),
            [ReportReasonEnum::REASON_ILLICIT_CONTENT],
            null
        );
        $report3->resolve();

        $report4 = new CitizenProjectReport(
            $this->getReference('citizen-project-2'),
            $this->getReference('adherent-4'),
            [ReportReasonEnum::REASON_ILLICIT_CONTENT],
            null
        );

        $report5 = new CitizenProjectReport(
            $this->getReference('citizen-project-2'),
            $this->getReference('adherent-4'),
            [ReportReasonEnum::REASON_ILLICIT_CONTENT],
            null
        );

        $report6 = new CitizenProjectReport(
            $this->getReference('citizen-project-3'),
            $this->getReference('adherent-4'),
            [ReportReasonEnum::REASON_ILLICIT_CONTENT],
            null
        );

        $manager->persist($report1);
        $manager->persist($report2);
        $manager->persist($report3);
        $manager->persist($report4);
        $manager->persist($report5);
        $manager->persist($report6);

        $manager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            LoadAdherentData::class,
            LoadCitizenProjectData::class,
        ];
    }
}

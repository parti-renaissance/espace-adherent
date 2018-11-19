<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\IdeasWorkshop\ConsultationReport;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class LoadConsultationReportData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $consultationReportGreenEnergy = new ConsultationReport(
            'Rapport sur les énergies renouvables',
            'https://fr.lipsum.com/'
        );

        $this->addReference('consultation-report-green-energy', $consultationReportGreenEnergy);

        $manager->persist($consultationReportGreenEnergy);

        $manager->flush();
    }
}

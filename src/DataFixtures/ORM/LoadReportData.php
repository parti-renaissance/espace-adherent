<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\Report\CitizenProjectReport;
use AppBundle\Entity\Report\IdeasWorkshop\IdeaReport;
use AppBundle\Entity\Report\IdeasWorkshop\ThreadCommentReport;
use AppBundle\Entity\Report\IdeasWorkshop\ThreadReport;
use AppBundle\Entity\Report\Report;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadReportData extends AbstractFixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $report1 = new CitizenProjectReport(
            $this->getReference('citizen-project-1'),
            $this->getReference('adherent-1'),
            [Report::REASON_INAPPROPRIATE],
            null
        );

        $report2 = new CitizenProjectReport(
            $this->getReference('citizen-project-1'),
            $this->getReference('adherent-2'),
            [Report::REASON_OTHER, Report::REASON_COMMERCIAL_CONTENT],
            'Ce projet n\'est pas intéressant.'
        );
        $report2->resolve();

        $report3 = new CitizenProjectReport(
            $this->getReference('citizen-project-1'),
            $this->getReference('adherent-3'),
            [Report::REASON_INAPPROPRIATE],
            null
        );
        $report3->resolve();

        $report4 = new CitizenProjectReport(
            $this->getReference('citizen-project-2'),
            $this->getReference('adherent-4'),
            [Report::REASON_INAPPROPRIATE],
            null
        );

        $report5 = new CitizenProjectReport(
            $this->getReference('citizen-project-2'),
            $this->getReference('adherent-4'),
            [Report::REASON_INAPPROPRIATE],
            null
        );

        $report6 = new CitizenProjectReport(
            $this->getReference('citizen-project-3'),
            $this->getReference('adherent-4'),
            [Report::REASON_INAPPROPRIATE],
            null
        );

        $report7 = new IdeaReport(
            $this->getReference('idea-disabled'),
            $this->getReference('adherent-4'),
            [Report::REASON_OTHER],
            'Je suis scandalisé!'
        );

        $report8 = new ThreadReport(
            $this->getReference('thread-comment-reported'),
            $this->getReference('adherent-4'),
            [Report::REASON_OTHER],
            'Je suis choqué!'
        );

        $report9 = new ThreadCommentReport(
            $this->getReference('thread-comment-reported'),
            $this->getReference('adherent-4'),
            [Report::REASON_OTHER],
            'Je suis décu...'
        );

        $manager->persist($report1);
        $manager->persist($report2);
        $manager->persist($report3);
        $manager->persist($report4);
        $manager->persist($report5);
        $manager->persist($report6);
        $manager->persist($report7);
        $manager->persist($report8);
        $manager->persist($report9);

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
            LoadIdeaData::class,
            LoadIdeaThreadData::class,
            LoadIdeaThreadCommentData::class,
        ];
    }
}

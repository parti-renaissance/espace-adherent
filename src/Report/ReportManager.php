<?php

namespace AppBundle\Report;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Report;
use AppBundle\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;

class ReportManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @throws \LogicException If $report is already resolved
     */
    public function resolve(Report $report): void
    {
        $report->resolve();
        $this->em->flush($report);
    }

    /**
     * @param string $type is one of the constant defined in \AppBundle\Report\ReportType class
     *
     * @throws \InvalidArgumentException if $type is not valid
     *
     * @return mixed|null Object matching $type and $uuid
     */
    public function getSubjectByUuid(string $type, string $uuid)
    {
        $reportFQCN = ReportType::getEntityFQCN($type);

        // By convention the subclass should call the doctrine attribute of the subject "subject". Like this we can get
        // the FQCN of the property thanks to Doctrine metadata
        $subjectFQCN = $this->em->getClassMetadata($reportFQCN)->getAssociationTargetClass('subject');

        return $this->em->getRepository($subjectFQCN)->findOneBy(['uuid' => $uuid]);
    }

    public function anonymAuthorReports(Adherent $adherent)
    {
        $this->getReportRepository()->anonymizeAuthorReports($adherent);
    }

    private function getReportRepository(): ReportRepository
    {
        return $this->em->getRepository(Report::class);
    }
}

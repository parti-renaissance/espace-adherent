<?php

namespace App\Report;

use App\Entity\Report\Report;
use App\Entity\Report\ReportableInterface;
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
        $this->em->flush();
    }

    /**
     * @param string $type is one of the constant defined in \App\Report\ReportType class
     *
     * @throws \InvalidArgumentException if $type is not valid
     *
     * @return ReportableInterface|null Object matching $type and $uuid
     */
    public function getSubjectByUuid(string $type, string $uuid): ?ReportableInterface
    {
        $reportFQCN = ReportType::getReportClassForType($type);

        // By convention the subclass should call the doctrine attribute of the subject "subject". Like this we can get
        // the FQCN of the property thanks to Doctrine metadata
        $subjectFQCN = $this->em->getClassMetadata($reportFQCN)->getAssociationTargetClass('subject');

        return $this->em->getRepository($subjectFQCN)->findOneBy(['uuid' => $uuid]);
    }
}

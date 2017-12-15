<?php

namespace AppBundle\Report;

use AppBundle\Entity\Report;
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

        // Get the FQCN
        $subjectFQCN = $this->em->getClassMetadata($reportFQCN)->getAssociationTargetClass('subject');

        return $this->em->getRepository($subjectFQCN)->findOneBy(['uuid' => $uuid]);
    }
}

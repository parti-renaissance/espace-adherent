<?php

namespace AppBundle\Report;

use AppBundle\Entity\CitizenProjectReport;
use Doctrine\ORM\EntityManagerInterface;

class ReportCreationCommandHandler
{
    private const MAPPING_TYPE_METHOD = [
        ReportType::CITIZEN_PROJECT => 'createCitizenProjectReport',
    ];

    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function handle(ReportCommand $command): void
    {
        $type = $command->getSubjectType();

        if (!isset(self::MAPPING_TYPE_METHOD[$type])) {
            throw new \RuntimeException("'$type' is not supported. You must add the corresponding private method.");
        }

        $report = call_user_func([$this, self::MAPPING_TYPE_METHOD[$type]], $command);

        $this->em->persist($report);
        $this->em->flush($report);
    }

    private function createCitizenProjectReport(ReportCommand $command): CitizenProjectReport
    {
        return new CitizenProjectReport(
            $command->getSubject(),
            $command->getAuthor(),
            $command->getReasons(),
            $command->getComment()
        );
    }
}

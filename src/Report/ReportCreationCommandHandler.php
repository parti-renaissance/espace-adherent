<?php

namespace App\Report;

use Doctrine\ORM\EntityManagerInterface;

class ReportCreationCommandHandler
{
    private $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    public function handle(ReportCommand $command): void
    {
        $subject = $command->getSubject();
        $reportClass = ReportType::getReportClassForSubject($subject);

        $this->em->persist(new $reportClass(
            $subject,
            $command->getAuthor(),
            $command->getReasons(),
            $command->getComment()
        ));
        $this->em->flush();
    }
}

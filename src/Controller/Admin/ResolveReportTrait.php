<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Report\ReportableInterface;
use AppBundle\Report\ReportManager;
use AppBundle\Repository\ReportRepository;

/**
 * This trait is used to resolve reports related to an reported object, when an user enable or disable this object.
 */
trait ResolveReportTrait
{
    protected function resolveReportsFor(
        string $class,
        ReportableInterface $object,
        ReportRepository $reportRepository,
        ReportManager $reportManager
    ): void {
        if ($reports = $reportRepository->findByClassAndSubject($class, $object)) {
            try {
                foreach ($reports as $report) {
                    $reportManager->resolve($report);
                }
            } catch (\LogicException $e) {
                throw new BadRequestHttpException($e->getMessage(), $e);
            }
        }
    }
}

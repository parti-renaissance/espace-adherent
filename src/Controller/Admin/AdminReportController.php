<?php

namespace App\Controller\Admin;

use App\Entity\Report\Report;
use App\Report\ReportManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/signalements')]
class AdminReportController extends AbstractController
{
    #[IsGranted('ROLE_APP_ADMIN_REPORT_APPROVE')]
    #[Route(path: '/{id}/resolve', name: 'app_admin_report_resolve', methods: ['GET'])]
    public function resolveAction(Request $request, Report $report, ReportManager $reportManager): Response
    {
        if (!$this->isCsrfTokenValid(\sprintf('report.%s', $report->getId()), $request->query->get('token'))) {
            throw new BadRequestHttpException('Invalid Csrf token provided.');
        }

        try {
            $reportManager->resolve($report);
            $this->addFlash('sonata_flash_success', \sprintf('Le signalement « %s » a été résolu avec succès.', $report->getId()));
        } catch (\LogicException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        return $this->redirectToRoute('admin_app_report_report_list');
    }
}

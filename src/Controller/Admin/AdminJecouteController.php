<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Jecoute\Survey;
use App\Exporter\SurveyExporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/questionnaires/survey/{id}/export', name: 'admin_app_jecoute_surveys_export')]
class AdminJecouteController extends AbstractController
{
    public function __invoke(
        Request $request,
        Survey $survey,
        SurveyExporter $exporter,
    ): Response {
        if ($survey->isNational()) {
            $this->denyAccessUnlessGranted('ROLE_ADMIN_APPLICATION_MOBILE_NATIONAL_SURVEYS');
        } else {
            $this->denyAccessUnlessGranted('ROLE_ADMIN_APPLICATION_MOBILE_LOCAL_SURVEYS');
        }

        return $exporter->export($survey, $request->query->get('format', 'csv'), true);
    }
}

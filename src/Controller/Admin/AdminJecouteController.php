<?php

namespace App\Controller\Admin;

use App\Entity\Jecoute\Survey;
use App\Exporter\SurveyExporter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/questionnaires')]
class AdminJecouteController
{
    /**
     * @IsGranted("ROLE_ADMIN_JECOUTE")
     */
    #[Route(path: '/survey/{id}/export', name: 'admin_app_jecoute_surveys_export', requirements: ['type' => 'local|national'])]
    public function nationalSurveyExportAction(
        Request $request,
        Survey $survey,
        SurveyExporter $exporter
    ): StreamedResponse {
        return $exporter->export($survey, $request->query->get('format', 'csv'), true);
    }
}

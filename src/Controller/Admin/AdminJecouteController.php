<?php

namespace App\Controller\Admin;

use App\Entity\Jecoute\Survey;
use App\Exporter\SurveyExporter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/questionnaires")
 */
class AdminJecouteController
{
    /**
     * @Route("/survey/{id}/export", name="admin_app_jecoute_surveys_export", requirements={"type": "local|national"})
     *
     * @Security("is_granted('ROLE_ADMIN_JECOUTE')")
     */
    public function nationalSurveyExportAction(
        Request $request,
        Survey $survey,
        SurveyExporter $exporter
    ): StreamedResponse {
        return $exporter->export($survey, $request->query->get('format', 'csv'), true);
    }
}

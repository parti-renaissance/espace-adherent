<?php

namespace App\Controller\Api\Phoning;

use App\Entity\Phoning\Campaign;
use App\Exporter\PhoningCampaignSurveyRepliesExporter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v3/phoning_campaigns/{uuid}/export-replies", name="api_export_phoning_campaigns_replies", requirements={"uuid": "%pattern_uuid%"}, methods={"GET"})
 * @Security("is_granted('IS_FEATURE_GRANTED', 'phoning_campaign')")
 */
class ExportPhoningCampaignSurveyAnswersController extends AbstractController
{
    public function __invoke(PhoningCampaignSurveyRepliesExporter $exporter, Campaign $campaign): Response
    {
        return $exporter->export($campaign, 'xls');
    }
}

<?php

namespace App\Controller\Api\Phoning;

use App\Entity\Phoning\Campaign;
use App\Exporter\PhoningCampaignSurveyRepliesExporter;
use App\Repository\Jecoute\DataSurveyRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/v3/phoning_campaigns/{uuid}/replies.{_format}",
 *     name="api_phoning_camapign_get_campaign_survey_replies",
 *     methods={"GET"},
 *     requirements={"uuid": "%pattern_uuid%", "_format": "json|csv|xls"},
 *     defaults={"_format": "json"}
 * )
 *
 * @Security("is_granted('IS_FEATURE_GRANTED', 'phoning_campaign')")
 */
class GetPhoningCampaignSurveyRepliesController extends AbstractController
{
    public function __invoke(
        Request $request,
        Campaign $campaign,
        string $_format,
        DataSurveyRepository $dataSurveyRepository,
        PhoningCampaignSurveyRepliesExporter $exporter
    ): Response {
        if ('json' !== $_format) {
            return $exporter->export($campaign, $_format);
        }

        return $this->json(
            $dataSurveyRepository->findPhoningCampaignDataSurveys(
                $campaign,
                $request->query->getInt('page', 1),
                $request->query->getInt('page_size', 30)
            ),
            Response::HTTP_OK,
            [],
            ['groups' => ['campaign_replies_list']]
        );
    }
}

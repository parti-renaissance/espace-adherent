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
 *     "/v3/phoning_campaigns/{uuid}/replies",
 *     name="api_phoning_camapign_get_campaign_survey_replies_",
 *     requirements={"uuid": "%pattern_uuid%"}
 * )
 *
 * @Security("is_granted('IS_FEATURE_GRANTED', 'phoning_campaign')")
 */
class GetPhoningCampaignSurveyRepliesController extends AbstractController
{
    /**
     * @Route(".{_format}", name="list", methods={"GET"}, defaults={"_format": "json"}, requirements={"_format": "json|csv|xls"})
     */
    public function list(
        Request $request,
        Campaign $campaign,
        string $_format,
        DataSurveyRepository $dataSurveyRepository,
        PhoningCampaignSurveyRepliesExporter $exporter
    ): Response {
        if ('json' !== $_format) {
            try {
                return $exporter->export($campaign, $_format);
            } catch (\Exception $e) {
                throw new \RuntimeException('An error occurred during the export', 0, $e);
            }
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

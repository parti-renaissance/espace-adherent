<?php

namespace App\Controller\Api\Phoning;

use App\Entity\Phoning\Campaign;
use App\Repository\Jecoute\DataSurveyRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/v3/phoning_campaigns/{uuid}/replies",
 *     name="api_phoning_camapign_get_campaign_survey_replies",
 *     methods={"GET"},
 *     requirements={"uuid": "%pattern_uuid%"}
 * )
 *
 * @Security("is_granted('IS_FEATURE_GRANTED', 'phoning_campaign')")
 */
class GetPhoningCampaignSurveyRepliesController extends AbstractController
{
    public function __invoke(Request $request, Campaign $campaign, DataSurveyRepository $dataSurveyRepository): Response
    {
        return $this->json(
            $dataSurveyRepository->findPhoningCampaignDataSurvey(
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

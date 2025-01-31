<?php

namespace App\Controller\Api\Phoning;

use App\Entity\Phoning\Campaign;
use App\Exporter\PhoningCampaignSurveyRepliesExporter;
use App\Repository\Jecoute\DataSurveyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', 'phoning_campaign')"))]
#[Route(path: '/v3/phoning_campaigns/{uuid}/replies.{_format}', name: 'api_phoning_campaign_get_campaign_survey_replies', requirements: ['uuid' => '%pattern_uuid%', '_format' => 'json|csv|xlsx'], defaults: ['_format' => 'json'], methods: ['GET'])]
class GetPhoningCampaignSurveyRepliesController extends AbstractController
{
    public function __invoke(
        Request $request,
        Campaign $campaign,
        string $_format,
        DataSurveyRepository $dataSurveyRepository,
        PhoningCampaignSurveyRepliesExporter $exporter,
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
            ['groups' => ['phoning_campaign_replies_list']]
        );
    }
}

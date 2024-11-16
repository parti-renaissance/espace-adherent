<?php

namespace App\Controller\Api\Pap;

use App\Entity\Pap\Campaign;
use App\Exporter\PapCampaignSurveyRepliesExporter;
use App\Repository\Jecoute\DataSurveyRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', ['pap_v2', 'pap']) and is_granted('SCOPE_CAN_MANAGE', campaign)"))]
#[Route(path: '/v3/pap_campaigns/{uuid}/replies.{_format}', name: 'api_pap_camapign_get_campaign_survey_replies', requirements: ['uuid' => '%pattern_uuid%', '_format' => 'json|csv|xlsx'], defaults: ['_format' => 'json'], methods: ['GET'])]
class GetPapCampaignSurveyRepliesController extends AbstractController
{
    public function __invoke(
        Request $request,
        Campaign $campaign,
        string $_format,
        DataSurveyRepository $dataSurveyRepository,
        PapCampaignSurveyRepliesExporter $exporter,
        ScopeGeneratorResolver $scopeGeneratorResolver,
    ): Response {
        $zones = [];
        $scope = $scopeGeneratorResolver->generate();
        if (!$scope->isNational()) {
            $zones = $scope->getZones();
        }

        if ('json' !== $_format) {
            return $exporter->export($campaign, $zones, $_format);
        }

        return $this->json(
            $dataSurveyRepository->findPapCampaignDataSurveys(
                $campaign,
                $zones,
                $request->query->getInt('page', 1),
                $request->query->getInt('page_size', 30)
            ),
            Response::HTTP_OK,
            [],
            ['groups' => ['pap_campaign_replies_list']]
        );
    }
}

<?php

namespace App\Controller\Api\Phoning;

use App\Entity\Phoning\Campaign;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("campaign.isPermanent() or is_granted('ROLE_PHONING_CAMPAIGN_MEMBER')"))]
#[Route(path: '/v3/phoning_campaigns/{uuid}/survey', name: 'api_phoning_camapign_get_campaign_survey', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
class GetPhoningCampaignSurveyController extends AbstractController
{
    public function __invoke(Campaign $campaign): Response
    {
        return $this->json(
            $campaign->getSurvey(),
            Response::HTTP_OK,
            [],
            ['groups' => ['survey_list']]
        );
    }
}

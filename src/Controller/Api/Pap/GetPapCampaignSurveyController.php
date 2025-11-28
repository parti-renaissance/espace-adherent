<?php

declare(strict_types=1);

namespace App\Controller\Api\Pap;

use App\Entity\Pap\Campaign;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')"))]
#[Route(path: '/v3/pap_campaigns/{uuid}/survey', name: 'api_pap_camapign_get_campaign_survey', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
class GetPapCampaignSurveyController extends AbstractController
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

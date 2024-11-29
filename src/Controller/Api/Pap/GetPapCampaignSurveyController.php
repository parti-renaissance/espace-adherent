<?php

namespace App\Controller\Api\Pap;

use App\Entity\Pap\Campaign;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/v3/pap_campaigns/{uuid}/survey', name: 'api_pap_camapign_get_campaign_survey', methods: ['GET'], requirements: ['uuid' => '%pattern_uuid%'])]
#[Security("is_granted('ROLE_OAUTH_SCOPE_JEMARCHE_APP') and is_granted('ROLE_PAP_USER')")]
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

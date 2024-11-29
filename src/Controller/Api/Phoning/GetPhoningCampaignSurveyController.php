<?php

namespace App\Controller\Api\Phoning;

use App\Entity\Phoning\Campaign;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/v3/phoning_campaigns/{uuid}/survey', name: 'api_phoning_camapign_get_campaign_survey', methods: ['GET'], requirements: ['uuid' => '%pattern_uuid%'])]
#[Security("campaign.isPermanent() or is_granted('ROLE_PHONING_CAMPAIGN_MEMBER')")]
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

<?php

namespace App\Controller\Api\Phoning;

use App\Entity\Phoning\Campaign;
use App\Repository\Jecoute\DataSurveyRepository;
use Symfony\Component\HttpFoundation\Request;

class GetPhoningCampaignSurveyRepliesController
{
    public function __invoke(Request $request, Campaign $data, DataSurveyRepository $dataSurveyRepository)
    {
        return $dataSurveyRepository->findPhoningCampaignDataSurvey(
            $data,
            $request->query->getInt('page', 1),
            $request->query->getInt('page_size', 30)
        );
    }
}

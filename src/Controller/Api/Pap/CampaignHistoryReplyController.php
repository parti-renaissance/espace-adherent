<?php

namespace App\Controller\Api\Pap;

use App\Controller\Api\Jecoute\AbstractReplyController;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\DataSurveyAwareInterface;
use App\Entity\Pap\CampaignHistory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CampaignHistoryReplyController extends AbstractReplyController
{
    public function __invoke(Request $request, CampaignHistory $campaignHistory): Response
    {
        return $this->handleRequest($request, $campaignHistory);
    }

    /**
     * @param CampaignHistory|DataSurveyAwareInterface $object
     */
    protected function initializeDataSurvey(Request $request, ?DataSurveyAwareInterface $object = null): DataSurvey
    {
        $dataSurvey = parent::initializeDataSurvey($request, $object);
        if ($object) {
            $dataSurvey->setSurvey($object->getCampaign()->getSurvey());
        }

        return $dataSurvey;
    }
}

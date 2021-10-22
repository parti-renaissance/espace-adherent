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
    /** @var CampaignHistory */
    private $campaignHistory;

    public function __invoke(Request $request, CampaignHistory $campaignHistory): Response
    {
        $this->campaignHistory = $campaignHistory;

        return $this->handleRequest($request, $campaignHistory);
    }

    /**
     * @param CampaignHistory|DataSurveyAwareInterface $object
     */
    protected function initializeDataSurvey(DataSurveyAwareInterface $object): DataSurvey
    {
        $dataSurvey = parent::initializeDataSurvey($object);
        $dataSurvey->setSurvey($object->getCampaign()->getSurvey());

        return $dataSurvey;
    }
}

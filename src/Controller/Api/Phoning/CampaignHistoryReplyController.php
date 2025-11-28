<?php

declare(strict_types=1);

namespace App\Controller\Api\Phoning;

use App\Controller\Api\Jecoute\AbstractReplyController;
use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\DataSurveyAwareInterface;
use App\Entity\Phoning\CampaignHistory;
use App\Phoning\CampaignHistoryStatusEnum;
use App\Security\Voter\CampaignHistoryCallerVoter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CampaignHistoryReplyController extends AbstractReplyController
{
    /** @var CampaignHistory */
    private $campaignHistory;

    public function __invoke(Request $request, CampaignHistory $campaignHistory): Response
    {
        $this->denyAccessUnlessGranted(CampaignHistoryCallerVoter::PERMISSION, $campaignHistory);

        $this->campaignHistory = $campaignHistory;

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

    protected function postHandleAction(): void
    {
        if ($author = $this->campaignHistory->getDataSurvey()->getAuthor()) {
            $this->campaignHistory->getDataSurvey()->setAuthorPostalCode($author->getPostalCode());
        }
        $this->campaignHistory->setStatus(CampaignHistoryStatusEnum::COMPLETED);
        $this->campaignHistory->setFinishAt(new \DateTime());
    }
}

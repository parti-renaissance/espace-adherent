<?php

declare(strict_types=1);

namespace App\Controller\Api\Jecoute;

use App\Entity\Jecoute\DataSurvey;
use App\Entity\Jecoute\DataSurveyAwareInterface;
use App\Entity\Jecoute\Survey;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SurveyReplyController extends AbstractReplyController
{
    public function __invoke(Request $request, Survey $survey): Response
    {
        return $this->handleRequest($request);
    }

    protected function initializeDataSurvey(Request $request, ?DataSurveyAwareInterface $object = null): DataSurvey
    {
        $dataSurvey = parent::initializeDataSurvey($request, $object);
        $dataSurvey->setSurvey($request->attributes->get('data'));

        return $dataSurvey;
    }
}

<?php

namespace App\Controller\Api\Jecoute;

use App\Entity\Jecoute\JemarcheDataSurvey;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JemarcheDataSurveyReplyController extends AbstractReplyController
{
    public const DESERIALIZE_GROUP = 'data_survey_write:include_survey';

    public function __invoke(Request $request, JemarcheDataSurvey $jemarcheDataSurvey): Response
    {
        return $this->handleRequest($request, $jemarcheDataSurvey);
    }

    protected function getCustomDeserializeGroups(): array
    {
        return [self::DESERIALIZE_GROUP];
    }
}

<?php

namespace App\Controller\Api\Jecoute;

use App\Entity\Jecoute\JemarcheDataSurvey;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class JemarcheDataSurveyReplyController extends AbstractReplyController
{
    public function __invoke(Request $request, JemarcheDataSurvey $jemarcheDataSurvey): Response
    {
        return $this->handleRequest($request, $jemarcheDataSurvey);
    }
}

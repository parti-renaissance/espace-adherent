<?php

namespace AppBundle\Controller\Api;

use AppBundle\Repository\SurveyRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/jecoute")
 */
class JecouteSurveyController
{
    /**
     * @Route("/survey", name="api_surveys_list")
     * @Method("GET")
     */
    public function surveyListAction(SurveyRepository $surveyRepository, Serializer $serializer): Response
    {
        return new JsonResponse(
            $serializer->serialize(
                $surveyRepository->findAllPublished(),
                'json',
                SerializationContext::create()->setGroups('survey_list')
            ),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }
}

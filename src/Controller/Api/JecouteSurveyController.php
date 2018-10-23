<?php

namespace AppBundle\Controller\Api;

use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Repository\SurveyRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/jecoute")
 */
class JecouteSurveyController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("/survey", name="api_surveys_list")
     * @Method("GET")
     */
    public function surveyListAction(SurveyRepository $surveyRepository, Serializer $serializer): Response
    {
        $this->disableInProduction();

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

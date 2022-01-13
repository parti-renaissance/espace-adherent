<?php

namespace App\Controller\Api\Jecoute;

use App\Entity\Jecoute\Survey;
use App\Exporter\SurveyExporter;
use App\Repository\Jecoute\DataSurveyRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     "/v3/surveys/{uuid}/replies.{_format}",
 *     name="api_survey_get_survey_replies",
 *     methods={"GET"},
 *     requirements={"uuid": "%pattern_uuid%", "_format": "json|csv|xls"},
 *     defaults={"_format": "json"}
 * )
 *
 * @Security("is_granted('IS_FEATURE_GRANTED', 'survey') and is_granted('CAN_READ_SURVEY', survey)")
 */
class GetSurveyRepliesController extends AbstractController
{
    public function __invoke(
        Request $request,
        Survey $survey,
        string $_format,
        DataSurveyRepository $dataSurveyRepository,
        SurveyExporter $exporter
    ): Response {
        if ('json' !== $_format) {
            return $exporter->export($survey, $_format);
        }

        return $this->json(
            $dataSurveyRepository->findDataSurveyForSurvey(
                $survey,
                [],
                $request->query->getInt('page', 1),
                $request->query->getInt('page_size', 30)
            ),
            Response::HTTP_OK,
            [],
            ['groups' => ['survey_replies_list']]
        );
    }
}

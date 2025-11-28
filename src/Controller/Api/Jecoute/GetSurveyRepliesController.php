<?php

declare(strict_types=1);

namespace App\Controller\Api\Jecoute;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\Survey;
use App\Exporter\SurveyExporter;
use App\Repository\Jecoute\DataSurveyRepository;
use App\Scope\ScopeGeneratorResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('REQUEST_SCOPE_GRANTED', 'survey') and is_granted('SCOPE_CAN_MANAGE', subject)"), subject: 'survey')]
#[Route(path: '/v3/surveys/{uuid}/replies.{_format}', name: 'api_survey_get_survey_replies', methods: ['GET'], requirements: ['uuid' => '%pattern_uuid%', '_format' => 'json|csv|xlsx'], defaults: ['_format' => 'json'])]
class GetSurveyRepliesController extends AbstractController
{
    public function __construct(private readonly ScopeGeneratorResolver $scopeGeneratorResolver)
    {
    }

    public function __invoke(
        Request $request,
        Survey $survey,
        string $_format,
        DataSurveyRepository $dataSurveyRepository,
        SurveyExporter $exporter,
    ): Response {
        $scope = $this->scopeGeneratorResolver->generate();

        if (!$scope) {
            throw new BadRequestHttpException('Unable to resolve scope from request.');
        }

        $zoneCodes = [];
        $user = $scope->getMainUser();

        if ($survey->isNational() && !$scope->isNational()) {
            /** @var Zone $zone */
            foreach ($this->getZones($user) as $zone) {
                switch ($zone->getType()) {
                    case Zone::DEPARTMENT:
                        $zoneCodes[] = $zone->getCode();
                        break;
                    case Zone::BOROUGH:
                        $department = current($zone->getParentsOfType(Zone::DEPARTMENT));
                        $zoneCodes[] = $department->getCode();
                        break;
                }
            }

            $zoneCodes = array_unique($zoneCodes);
        }

        if ('json' !== $_format) {
            return $exporter->export($survey, $_format, false, [], $zoneCodes);
        }

        return $this->json(
            $dataSurveyRepository->findDataSurveyForSurvey(
                $survey,
                [],
                $zoneCodes,
                $request->query->getInt('page', 1),
                $request->query->getInt('page_size', 30)
            ),
            Response::HTTP_OK,
            [],
            ['groups' => ['survey_replies_list']]
        );
    }

    protected function getZones(Adherent $adherent): array
    {
        if ($adherent->isCorrespondent()) {
            return [$adherent->getCorrespondentZone()];
        }

        return [];
    }
}

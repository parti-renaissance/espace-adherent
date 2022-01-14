<?php

namespace App\Controller\Api\Jecoute;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Jecoute\Survey;
use App\Exporter\SurveyExporter;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Jecoute\DataSurveyRepository;
use App\Scope\AuthorizationChecker;
use App\Scope\ScopeEnum;
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
    private AuthorizationChecker $authorizationChecker;
    private ZoneRepository $zoneRepository;

    public function __construct(AuthorizationChecker $authorizationChecker, ZoneRepository $zoneRepository)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->zoneRepository = $zoneRepository;
    }

    public function __invoke(
        Request $request,
        Survey $survey,
        string $_format,
        DataSurveyRepository $dataSurveyRepository,
        SurveyExporter $exporter
    ): Response {
        /** @var Adherent $user */
        $user = $this->getUser();

        $scopeGenerator = $this->authorizationChecker->getScopeGenerator(
            $request,
            $user
        );

        $user = $scopeGenerator->isDelegatedAccess() ? $scopeGenerator->getDelegatedAccess()->getDelegator() : $user;

        $zoneCodes = [];
        if (ScopeEnum::REFERENT === $scopeGenerator->getCode() && $survey->isNational()) {
            $zones = $this->getZones($user);

            /** @var Zone $zone */
            foreach ($zones as $zone) {
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
        return $this->zoneRepository->findForJecouteByReferentTags($adherent->getManagedArea()->getTags()->toArray());
    }
}

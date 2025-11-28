<?php

declare(strict_types=1);

namespace App\Controller\EnMarche\Jecoute;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Adherent;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Jecoute\SurveyQuestion;
use App\Exporter\SurveyExporter;
use App\Form\Jecoute\SurveyFormType;
use App\Jecoute\JecouteSpaceEnum;
use App\Jecoute\StatisticsProvider;
use App\Jecoute\SurveyTypeEnum;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Jecoute\DataAnswerRepository;
use App\Repository\Jecoute\LocalSurveyRepository;
use App\Repository\Jecoute\NationalSurveyRepository;
use App\Repository\Jecoute\SuggestedQuestionRepository;
use Doctrine\ORM\EntityManagerInterface as ObjectManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

abstract class AbstractJecouteController extends AbstractController
{
    use AccessDelegatorTrait;

    protected $localSurveyRepository;
    protected $zoneRepository;
    private $nationalSurveyRepository;

    public function __construct(
        LocalSurveyRepository $localSurveyRepository,
        NationalSurveyRepository $nationalSurveyRepository,
        ZoneRepository $zoneRepository,
    ) {
        $this->localSurveyRepository = $localSurveyRepository;
        $this->nationalSurveyRepository = $nationalSurveyRepository;
        $this->zoneRepository = $zoneRepository;
    }

    #[Route(path: '', name: 'local_surveys_list', methods: ['GET'], defaults: ['type' => SurveyTypeEnum::LOCAL])]
    #[Route(path: '/questionnaires-nationaux', name: 'national_surveys_list', methods: ['GET'], defaults: ['type' => SurveyTypeEnum::NATIONAL])]
    public function jecouteSurveysListAction(Request $request, string $type): Response
    {
        return $this->renderTemplate('jecoute/surveys_list.html.twig', [
            'type' => $type,
            'surveys' => SurveyTypeEnum::LOCAL === $type ? $this->getLocalSurveys($this->getMainUser($request->getSession())) : $this->nationalSurveyRepository->findAllPublishedWithStats(),
        ]);
    }

    #[Route(path: '/creer', name: 'local_survey_create', methods: ['GET|POST'])]
    public function jecouteSurveyCreateAction(
        Request $request,
        ObjectManager $manager,
        SuggestedQuestionRepository $suggestedQuestionRepository,
    ): Response {
        $this->checkCreateAccess();

        /** @var Adherent $user */
        $user = $this->getUser();

        $localSurvey = LocalSurvey::create($user);
        $zones = $this->getZones($this->getMainUser($request->getSession()));
        if (1 === \count($zones)) {
            $localSurvey->setZone($zones[0]);
        }

        $form = $this
            ->createForm(SurveyFormType::class, $localSurvey, ['zones' => $zones, 'edit_by_author' => true])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($form->getData());
            $manager->flush();

            $this->addFlash('info', 'survey.create.success');

            return $this->redirectToJecouteRoute('local_surveys_list');
        }

        return $this->renderTemplate('jecoute/create.html.twig', [
            'form' => $form->createView(),
            'suggestedQuestions' => $suggestedQuestionRepository->findAllPublished(),
        ]);
    }

    #[IsGranted('CAN_EDIT_SURVEY', subject: 'survey')]
    #[Route(path: '/questionnaire/{uuid}/editer', name: 'local_survey_edit', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET|POST'])]
    public function jecouteSurveyEditAction(
        Request $request,
        LocalSurvey $survey,
        ObjectManager $manager,
        SuggestedQuestionRepository $suggestedQuestionRepository,
    ): Response {
        $author = $survey->getCreator();
        if ($editByAuthor = ($author === $this->getMainUser($request->getSession()))) {
            $zones = $this->getZones($author);
        } else {
            $zones = [$survey->getZone()];
        }

        $form = $this
            ->createForm(SurveyFormType::class, $survey, ['zones' => $zones, 'edit_by_author' => $editByAuthor])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('info', 'survey.edit.success');

            return $this->redirectToJecouteRoute('local_surveys_list');
        }

        return $this->renderTemplate('jecoute/create.html.twig', [
            'form' => $form->createView(),
            'suggestedQuestions' => $suggestedQuestionRepository->findAllPublished(),
        ]);
    }

    #[Route(path: '/questionnaire/{uuid}', name: 'survey_show', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
    public function jecouteSurveyShowAction(
        #[MapEntity(expr: 'repository.findOnePublishedByUuid(uuid)')]
        Survey $survey,
    ): Response {
        $isLocalSurvey = $survey instanceof LocalSurvey;
        $form = $this->createForm(SurveyFormType::class, $survey, [
            'zones' => $isLocalSurvey ? [$survey->getZone()] : [],
            'disabled' => true,
        ]);

        return $this->renderTemplate('jecoute/show.html.twig', [
            'form' => $form->createView(),
            'survey_type' => $isLocalSurvey ? 'local' : 'national',
        ]);
    }

    #[IsGranted(new Expression("(is_granted('IS_AUTHOR_OF', subject) or is_granted('IS_SURVEY_MANAGER_OF', subject)) or subject.isNational()"), subject: 'survey')]
    #[Route(path: '/questionnaire/{uuid}/stats', name: 'survey_stats', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
    public function jecouteSurveyStatsAction(
        Request $request,
        #[MapEntity(expr: 'repository.findOneByUuid(uuid)')]
        Survey $survey,
        StatisticsProvider $provider,
        SurveyExporter $exporter,
    ): Response {
        if ($format = $request->query->get('export')) {
            if ($survey instanceof LocalSurvey) {
                $surveyZone = $survey->getZone();
                $zones = $this->getZones($this->getMainUser($request->getSession()));
                $isParentZone = false;

                foreach ($zones as $zone) {
                    if (\in_array($surveyZone, $zone->getParents())) {
                        $isParentZone = true;

                        break;
                    }
                }

                if ($isParentZone) {
                    return $exporter->export($survey, $format, false, $zones);
                }
            }

            return $exporter->export($survey, $format, false);
        }

        return $this->renderTemplate('jecoute/stats.html.twig', ['data' => $provider->getStatsBySurvey($survey)]);
    }

    #[IsGranted(new Expression("is_granted('IS_AUTHOR_OF', subject) or is_granted('IS_SURVEY_MANAGER_OF', subject)"), subject: 'survey')]
    #[Route(path: '/questionnaire/{uuid}/dupliquer', name: 'local_survey_duplicate', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
    public function jecouteSurveyDuplicateAction(
        #[MapEntity(expr: 'repository.findOneByUuid(uuid)')]
        LocalSurvey $survey,
        ObjectManager $manager,
    ): Response {
        $clonedSurvey = clone $survey;

        $manager->persist($clonedSurvey);
        $manager->flush();

        $this->addFlash('info', 'survey.duplicate.success');

        return $this->redirectToJecouteRoute('local_surveys_list');
    }

    #[IsGranted(new Expression("is_granted('IS_AUTHOR_OF', subject.getSurvey()) or is_granted('IS_SURVEY_MANAGER_OF', subject.getSurvey()) or subject.getSurvey().isNational()"), subject: 'surveyQuestion')]
    #[Route(path: '/question/{uuid}/reponses', name: 'survey_stats_answers_list', condition: 'request.isXmlHttpRequest()')]
    public function jecouteSurveyAnswersListAction(
        SurveyQuestion $surveyQuestion,
        DataAnswerRepository $dataAnswerRepository,
    ): Response {
        return $this->render('jecoute/data_answers_dialog_content.html.twig', [
            'answers' => $dataAnswerRepository->findAllBySurveyQuestion($surveyQuestion->getUuid()),
        ]);
    }

    abstract protected function getSpaceName(): string;

    abstract protected function getZones(Adherent $adherent): array;

    /**
     * @return LocalSurvey[]
     */
    protected function getLocalSurveys(Adherent $adherent): array
    {
        return $this->localSurveyRepository->findAllByZonesWithStats($this->getZones($adherent));
    }

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => \sprintf('jecoute/_base_%s_space.html.twig', $spaceName = $this->getSpaceName()),
                'space_name' => $spaceName,
            ]
        ));
    }

    protected function redirectToJecouteRoute(string $subName, array $parameters = []): Response
    {
        return $this->redirectToRoute("app_jecoute_{$this->getSpaceName()}_$subName", $parameters);
    }

    protected function checkCreateAccess(): void
    {
        if (JecouteSpaceEnum::CANDIDATE_SPACE === $this->getSpaceName()
            && $this->isGranted('ROLE_CANDIDATE_DEPARTMENTAL')) {
            throw $this->createAccessDeniedException('You have no permission to create a survey');
        }
    }
}

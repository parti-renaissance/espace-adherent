<?php

namespace App\Controller\EnMarche\Jecoute;

use App\Entity\Adherent;
use App\Entity\Jecoute\LocalSurvey;
use App\Entity\Jecoute\NationalSurvey;
use App\Entity\Jecoute\Survey;
use App\Entity\Jecoute\SurveyQuestion;
use App\Form\Jecoute\SurveyFormType;
use App\Jecoute\StatisticsExporter;
use App\Jecoute\StatisticsProvider;
use App\Jecoute\SurveyTypeEnum;
use App\Repository\Jecoute\DataAnswerRepository;
use App\Repository\Jecoute\LocalSurveyRepository;
use App\Repository\Jecoute\NationalSurveyRepository;
use App\Repository\Jecoute\SuggestedQuestionRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Gedmo\Sluggable\Util\Urlizer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractJecouteController extends Controller
{
    protected $localSurveyRepository;
    private $nationalSurveyRepository;

    public function __construct(
        LocalSurveyRepository $localSurveyRepository,
        NationalSurveyRepository $nationalSurveyRepository
    ) {
        $this->localSurveyRepository = $localSurveyRepository;
        $this->nationalSurveyRepository = $nationalSurveyRepository;
    }

    /**
     * @Route("", name="local_surveys_list", methods={"GET"}, defaults={"type": SurveyTypeEnum::LOCAL})
     * @Route("/questionnaires-nationaux", name="national_surveys_list", methods={"GET"}, defaults={"type": SurveyTypeEnum::NATIONAL})
     */
    public function jecouteSurveysListAction(string $type): Response
    {
        return $this->renderTemplate('jecoute/surveys_list.html.twig', [
            'type' => $type,
            'surveys' => $this->getListSurveys($type),
        ]);
    }

    /**
     * @Route(
     *     path="/questionnaire/creer",
     *     name="local_survey_create",
     *     methods={"GET|POST"},
     * )
     */
    public function jecouteSurveyCreateAction(
        Request $request,
        ObjectManager $manager,
        SuggestedQuestionRepository $suggestedQuestionRepository,
        UserInterface $user
    ): Response {
        /** @var Adherent $user */
        $localSurvey = new LocalSurvey($user);

        $form = $this
            ->createSurveyForm($localSurvey)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $localSurvey->setTags($this->getSurveyTags());

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

    /**
     * @Route(
     *     path="/questionnaire/{uuid}/editer",
     *     name="local_survey_edit",
     *     requirements={"uuid": "%pattern_uuid%"},
     *     methods={"GET|POST"}
     * )
     *
     * @Security("is_granted('IS_AUTHOR_OF', survey) or is_granted('IS_SURVEY_MANAGER_OF', survey)")
     */
    public function jecouteSurveyEditAction(
        Request $request,
        LocalSurvey $survey,
        ObjectManager $manager,
        SuggestedQuestionRepository $suggestedQuestionRepository
    ): Response {
        $form = $this
            ->createSurveyForm($survey)
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

    /**
     * @Route(
     *     path="/questionnaire/{uuid}",
     *     name="national_survey_show",
     *     requirements={"uuid": "%pattern_uuid%"},
     *     methods={"GET"}
     * )
     *
     * @Entity("nationalSurvey", expr="repository.findOnePublishedByUuid(uuid)")
     */
    public function jecouteNationalSurveyShowAction(NationalSurvey $nationalSurvey): Response
    {
        $form = $this->createForm(
            SurveyFormType::class, $nationalSurvey, ['disabled' => true]
        );

        return $this->renderTemplate('jecoute/show.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     path="/questionnaire/{uuid}/stats",
     *     name="survey_stats",
     *     requirements={"uuid": "%pattern_uuid%"},
     *     methods={"GET"}
     * )
     *
     * @Entity("survey", expr="repository.findOneByUuid(uuid)")
     *
     * @Security("(is_granted('IS_AUTHOR_OF', survey) or is_granted('IS_SURVEY_MANAGER_OF', survey)) or survey.isNational()")
     */
    public function jecouteSurveyStatsAction(
        Request $request,
        Survey $survey,
        StatisticsProvider $provider,
        StatisticsExporter $exporter
    ): Response {
        $data = $provider->getStatsBySurvey($survey);

        if ($request->query->has('export')) {
            return new Response(
                $exporter->export($data),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment;filename="'.Urlizer::urlize($survey->getName()).'-'.date('Y-m-d_H-i').'.csv"',
                ]
            );
        }

        return $this->renderTemplate('jecoute/stats.html.twig', ['data' => $data]);
    }

    /**
     * @Route(
     *     path="/questionnaire/{uuid}/dupliquer",
     *     name="local_survey_duplicate",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Entity("survey", expr="repository.findOneByUuid(uuid)")
     *
     * @Security("is_granted('IS_AUTHOR_OF', survey) or is_granted('IS_SURVEY_MANAGER_OF', survey)")
     */
    public function jecouteSurveyDuplicateAction(LocalSurvey $survey, ObjectManager $manager): Response
    {
        $clonedSurvey = clone $survey;

        $manager->persist($clonedSurvey);
        $manager->flush();

        $this->addFlash('info', 'survey.duplicate.success');

        return $this->redirectToJecouteRoute('local_surveys_list');
    }

    /**
     * @Route(
     *     path="/question/{uuid}/reponses",
     *     name="survey_stats_answers_list",
     *     condition="request.isXmlHttpRequest()",
     * )
     *
     * @Security("is_granted('IS_AUTHOR_OF', surveyQuestion) or is_granted('IS_SURVEY_MANAGER_OF', surveyQuestion.getSurvey())")
     */
    public function jecouteSurveyAnswersListAction(
        SurveyQuestion $surveyQuestion,
        DataAnswerRepository $dataAnswerRepository
    ): Response {
        return $this->render('jecoute/data_answers_dialog_content.html.twig', [
            'answers' => $dataAnswerRepository->findAllBySurveyQuestion($surveyQuestion->getUuid()),
        ]);
    }

    abstract protected function getSpaceName(): string;

    /**
     * @return LocalSurvey[]
     */
    abstract protected function getLocalSurveys(): array;

    abstract protected function getSurveyTags(): array;

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('jecoute/_base_%s_space.html.twig', $spaceName = $this->getSpaceName()),
                'space_name' => $spaceName,
            ]
        ));
    }

    protected function redirectToJecouteRoute(string $subName, array $parameters = []): Response
    {
        return $this->redirectToRoute("app_jecoute_{$this->getSpaceName()}_${subName}", $parameters);
    }

    protected function createSurveyForm(LocalSurvey $localSurvey): FormInterface
    {
        return $this->createForm(SurveyFormType::class, $localSurvey);
    }

    /**
     * @return LocalSurvey[]|NationalSurvey[]
     */
    private function getListSurveys(string $type): array
    {
        if (SurveyTypeEnum::LOCAL === $type) {
            return $this->getLocalSurveys();
        }

        return $this->nationalSurveyRepository->findAllPublishedWithStats();
    }
}

<?php

namespace AppBundle\Controller\EnMarche\Jecoute;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Jecoute\LocalSurvey;
use AppBundle\Entity\Jecoute\NationalSurvey;
use AppBundle\Entity\Jecoute\Survey;
use AppBundle\Entity\Jecoute\SurveyQuestion;
use AppBundle\Form\Jecoute\SurveyFormType;
use AppBundle\Jecoute\StatisticsExporter;
use AppBundle\Jecoute\StatisticsProvider;
use AppBundle\Jecoute\SurveyExporter;
use AppBundle\Repository\Jecoute\DataAnswerRepository;
use AppBundle\Repository\Jecoute\LocalSurveyRepository;
use AppBundle\Repository\Jecoute\NationalSurveyRepository;
use AppBundle\Repository\Jecoute\SuggestedQuestionRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class AbstractJecouteController extends Controller
{
    /**
     * @Route(
     *     name="local_surveys_list",
     *     methods={"GET"},
     * )
     */
    public function jecouteLocalSurveysListAction(
        LocalSurveyRepository $localSurveyRepository,
        SurveyExporter $surveyExporter,
        UserInterface $user
    ): Response {
        /** @var Adherent $user */
        $tags = $user->isJecouteManager() ? $user->getJecouteManagedArea()->getCodes() :
            $user->getManagedAreaTagCodes()
        ;

        return  $this->renderTemplate('jecoute/local_surveys_list.html.twig', [
            'surveysListJson' => $surveyExporter->exportLocalSurveysAsJson(
                $localSurveyRepository->findAllByTags($tags),
                $this->getSpaceName()
            ),
        ]);
    }

    /**
     * @Route(
     *     path="/questionnaires-nationaux",
     *     name="national_surveys_list",
     *     methods={"GET"},
     * )
     */
    public function jecouteNationalSurveysListAction(
        NationalSurveyRepository $nationalSurveyRepository,
        SurveyExporter $surveyExporter
    ): Response {
        return $this->renderTemplate('jecoute/national_surveys_list.html.twig', [
            'surveysListJson' => $surveyExporter->exportNationalSurveysAsJson(
                $nationalSurveyRepository->findAllPublished(),
                $this->getSpaceName()
            ),
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
            ->createForm(SurveyFormType::class, $localSurvey)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $tags = $user->isJecouteManager() ? $user->getJecouteManagedArea()->getCodes() :
                $user->getManagedArea()->getReferentTagCodes()
            ;

            $localSurvey->setTags($tags);

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
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET|POST"},
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
            ->createForm(SurveyFormType::class, $survey)
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
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
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
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Entity("survey", expr="repository.findOneByUuid(uuid)")
     */
    public function jecouteSurveyStatsAction(Survey $survey, StatisticsProvider $provider): Response
    {
        return $this->renderTemplate('jecoute/stats.html.twig', [
            'data' => $provider->getStatsBySurvey($survey),
        ]);
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
            'answers' => $dataAnswerRepository->findAllBySurveyQuestion($surveyQuestion),
        ]);
    }

    /**
     * @Route(
     *     path="/questionnaire/{uuid}/stats/download",
     *     name="survey_stats_download",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Entity("survey", expr="repository.findOneByUuid(uuid)")
     *
     * @Security("(is_granted('IS_AUTHOR_OF', survey) or is_granted('IS_SURVEY_MANAGER_OF', survey)) or survey.isNational()")
     */
    public function jecouteSurveyStatsDownloadAction(Survey $survey, StatisticsExporter $statisticsExporter): Response
    {
        $dataFile = $statisticsExporter->export($survey);

        return new Response($dataFile['content'], Response::HTTP_OK, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment;filename="'.$dataFile['filename'].'"',
        ]);
    }

    abstract protected function getSpaceName(): string;

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
}

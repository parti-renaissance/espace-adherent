<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Address\GeoCoder;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Entity\InstitutionalEvent;
use AppBundle\Entity\Jecoute\LocalSurvey;
use AppBundle\Entity\Jecoute\NationalSurvey;
use AppBundle\Entity\Jecoute\Survey;
use AppBundle\Entity\Jecoute\SurveyQuestion;
use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Entity\ReferentOrganizationalChart\PersonOrganizationalChartItem;
use AppBundle\Entity\ReferentOrganizationalChart\ReferentPersonLink;
use AppBundle\Event\EventCommand;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Form\EventCommandType;
use AppBundle\Form\InstitutionalEventCommandType;
use AppBundle\Form\Jecoute\SurveyFormType;
use AppBundle\Form\PotentialCoReferentsType;
use AppBundle\Form\ReferentMessageType;
use AppBundle\Form\ReferentPersonLinkType;
use AppBundle\InstitutionalEvent\InstitutionalEventCommand;
use AppBundle\InstitutionalEvent\InstitutionalEventCommandHandler;
use AppBundle\Jecoute\StatisticsExporter;
use AppBundle\Jecoute\StatisticsProvider;
use AppBundle\Referent\ManagedCitizenProjectsExporter;
use AppBundle\Referent\ManagedCommitteesExporter;
use AppBundle\Referent\ManagedEventsExporter;
use AppBundle\Referent\ManagedInstitutionalEventsExporter;
use AppBundle\Referent\ManagedUsersFilter;
use AppBundle\Referent\ReferentMessage;
use AppBundle\Referent\ReferentMessageNotifier;
use AppBundle\Referent\SurveyExporter;
use AppBundle\Repository\CitizenProjectRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Repository\EventRepository;
use AppBundle\Repository\InstitutionalEventRepository;
use AppBundle\Repository\Jecoute\DataAnswerRepository;
use AppBundle\Repository\Jecoute\LocalSurveyRepository;
use AppBundle\Repository\Jecoute\NationalSurveyRepository;
use AppBundle\Repository\Jecoute\SuggestedQuestionRepository;
use AppBundle\Repository\ReferentOrganizationalChart\OrganizationalChartItemRepository;
use AppBundle\Repository\ReferentOrganizationalChart\ReferentPersonLinkRepository;
use AppBundle\Repository\ReferentRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * All the route names should start with 'app_referent_', if not you should modify AppBundle\EventListener\RecordReferentLastVisitListener.
 *
 * @Route("/espace-referent")
 */
class ReferentController extends Controller
{
    public const TOKEN_ID = 'referent_managed_users';

    /**
     * @Route("/utilisateurs", name="app_referent_users", methods={"GET"})
     *
     * @Security("is_granted('ROLE_REFERENT') or is_granted('ROLE_COREFERENT')")
     */
    public function usersAction(Request $request): Response
    {
        $filter = new ManagedUsersFilter();
        $filter->handleRequest($request);

        if ($filter->hasToken() && !$this->isCsrfTokenValid(self::TOKEN_ID, $filter->getToken())) {
            return $this->redirectToRoute('app_referent_users');
        }

        $repository = $this->getDoctrine()->getRepository(ReferentManagedUser::class);
        $referent = $this->getUser()->isCoReferent() ? $this->getUser()->getReferentOfReferentTeam() : $this->getUser();
        $results = $repository->search($referent, $filter->hasToken() ? $filter : null);

        $filter->setToken($this->get('security.csrf.token_manager')->getToken(self::TOKEN_ID));

        return $this->render('referent/users_list.html.twig', [
            'filter' => $filter,
            'has_filter' => $request->query->has(ManagedUsersFilter::PARAMETER_TOKEN),
            'results_count' => $results->count(),
            'results' => $results->getQuery()->getResult(),
        ]);
    }

    /**
     * @Route("/utilisateurs/message", name="app_referent_users_message", methods={"GET", "POST"})
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function usersSendMessageAction(Request $request): Response
    {
        $filter = new ManagedUsersFilter();
        $filter->handleRequest($request);

        if ($filter->hasToken() && !$this->isCsrfTokenValid(self::TOKEN_ID, $filter->getToken())) {
            return $this->redirectToRoute('app_referent_users');
        }

        $message = ReferentMessage::create($this->getUser(), $filter);

        $form = $this->createForm(ReferentMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get(ReferentMessageNotifier::class)->sendMessage($message);
            $this->addFlash('info', 'referent.message.success');

            return $this->redirect($this->generateUrl('app_referent_users').$filter);
        }

        $repository = $this->getDoctrine()->getRepository(ReferentManagedUser::class);
        $results = $repository->search($this->getUser(), $filter->hasToken() ? $filter : null, true);

        return $this->render('referent/users_message.html.twig', [
            'filter' => $filter,
            'results_count' => $results->count(),
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/evenements", name="app_referent_events", methods={"GET"})
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function eventsAction(EventRepository $eventRepository, ManagedEventsExporter $eventsExporter): Response
    {
        return $this->render('referent/events_list.html.twig', [
            'managedEventsJson' => $eventsExporter->exportAsJson($eventRepository->findManagedBy($this->getUser())),
        ]);
    }

    /**
     * @Route("/evenements/creer", name="app_referent_events_create", methods={"GET", "POST"})
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function eventsCreateAction(Request $request, GeoCoder $geoCoder): Response
    {
        $command = new EventCommand($this->getUser());
        $command->setTimeZone($geoCoder->getTimezoneFromIp($request->getClientIp()));
        $form = $this->createForm(EventCommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Event $event */
            $event = $this->get('app.event.handler')->handle($command);

            $registrationCommand = new EventRegistrationCommand($event, $this->getUser());
            $this->get('app.event.registration_handler')->handle($registrationCommand);

            $this->addFlash('info', 'referent.event.creation.success');

            return $this->redirectToRoute('app_event_show', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('referent/event_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/evenements-institutionnels", name="app_referent_institutional_events", methods={"GET"})
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function institutionalEventsAction(
        InstitutionalEventRepository $institutionalEventRepository,
        ManagedInstitutionalEventsExporter $exporter
    ): Response {
        return $this->render('referent/institutional_events/list.html.twig', [
            'managedInstitutionalEventsJson' => $exporter->exportAsJson(
                $institutionalEventRepository->findByOrganizer($this->getUser())
            ),
        ]);
    }

    /**
     * @Route("/evenements-institutionnels/creer", name="app_referent_institutional_events_create", methods={"GET", "POST"})
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function institutionalEventsCreateAction(
        Request $request,
        InstitutionalEventCommandHandler $institutionalEventCommandHandler,
        GeoCoder $geoCoder
    ): Response {
        $command = new InstitutionalEventCommand($this->getUser());
        $command->setTimeZone($geoCoder->getTimezoneFromIp($request->getClientIp()));

        $form = $this
            ->createForm(InstitutionalEventCommandType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $institutionalEventCommandHandler->handle($command);

            $this->addFlash('info', 'referent.institutional_event.create.success');

            return $this->redirectToRoute('app_referent_institutional_events');
        }

        return $this->render('referent/institutional_events/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     path="/evenements-institutionnels/{uuid}/editer",
     *     name="app_referent_institutional_events_edit",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET", "POST"}
     * )
     *
     * @Security("is_granted('ROLE_REFERENT') and is_granted('IS_AUTHOR_OF', institutionalEvent)")
     */
    public function institutionalEventsEditAction(
        Request $request,
        InstitutionalEvent $institutionalEvent,
        InstitutionalEventCommandHandler $institutionalEventCommandHandler
    ): Response {
        $form = $this
            ->createForm(
                InstitutionalEventCommandType::class,
                $command = InstitutionalEventCommand::createFromInstitutionalEvent($institutionalEvent),
                ['view' => InstitutionalEventCommandType::EDIT_VIEW]
            )
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $institutionalEventCommandHandler->handleUpdate($command, $institutionalEvent);

            $this->addFlash('info', 'referent.institutional_event.update.success');

            return $this->redirectToRoute('app_referent_institutional_events');
        }

        return $this->render('referent/institutional_events/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     path="/evenements-institutionnels/{uuid}/supprimer",
     *     name="app_referent_institutional_events_delete",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"}
     * )
     *
     * @Security("is_granted('ROLE_REFERENT') and is_granted('IS_AUTHOR_OF', institutionalEvent)")
     */
    public function institutionalEventsDeleteAction(
        InstitutionalEvent $institutionalEvent,
        InstitutionalEventCommandHandler $institutionalEventCommandHandler
    ): Response {
        $institutionalEventCommandHandler->handleDelete($institutionalEvent);

        $this->addFlash('info', 'referent.institutional_event.delete.success');

        return $this->redirectToRoute('app_referent_institutional_events');
    }

    /**
     * @Route("/comites", name="app_referent_committees", methods={"GET"})
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function committeesAction(
        CommitteeRepository $committeeRepository,
        ManagedCommitteesExporter $committeesExporter
    ): Response {
        return $this->render('referent/base_group_list.html.twig', [
            'title' => 'Comités',
            'managedGroupsJson' => $committeesExporter->exportAsJson($committeeRepository->findManagedBy($this->getUser())),
        ]);
    }

    /**
     * @Route("/projets-citoyens", name="app_referent_citizen_projects", methods={"GET"})
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function citizenProjectsAction(
        CitizenProjectRepository $citizenProjectRepository,
        ManagedCitizenProjectsExporter $citizenProjectsExporter
    ): Response {
        return $this->render('referent/base_group_list.html.twig', [
            'title' => 'Projets citoyens',
            'managedGroupsJson' => $citizenProjectsExporter->exportAsJson($citizenProjectRepository->findManagedByReferent($this->getUser())),
        ]);
    }

    /**
     * @Route(
     *     path="/jecoute/questionnaires-locaux",
     *     name="app_referent_jecoute_local_surveys_list",
     *     methods={"GET"},
     * )
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function jecouteLocalSurveysListAction(
        LocalSurveyRepository $localSurveyRepository,
        SurveyExporter $surveyExporter,
        UserInterface $user
    ): Response {
        /** @var Adherent $user */
        return $this->render('referent/surveys/local_surveys_list.html.twig', [
            'surveysListJson' => $surveyExporter->exportLocalSurveysAsJson(
                $localSurveyRepository->findAllByAuthor($user)
            ),
        ]);
    }

    /**
     * @Route(
     *     path="/jecoute/questionnaires-nationaux",
     *     name="app_referent_jecoute_national_surveys_list",
     *     methods={"GET"},
     * )
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function jecouteNationalSurveysListAction(
        NationalSurveyRepository $nationalSurveyRepository,
        SurveyExporter $surveyExporter
    ): Response {
        return $this->render('referent/surveys/national_surveys_list.html.twig', [
            'surveysListJson' => $surveyExporter->exportNationalSurveysAsJson(
                $nationalSurveyRepository->findAllPublished()
            ),
        ]);
    }

    /**
     * @Route(
     *     path="/jecoute/questionnaire/creer",
     *     name="app_referent_jecoute_local_survey_create",
     *     methods={"GET|POST"},
     * )
     *
     * @Security("is_granted('ROLE_REFERENT')")
     */
    public function jecouteSurveyCreateAction(
        Request $request,
        ObjectManager $manager,
        SuggestedQuestionRepository $suggestedQuestionRepository,
        UserInterface $user
    ): Response {
        /** @var Adherent $user */
        $form = $this
            ->createForm(SurveyFormType::class, new LocalSurvey($user))
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($form->getData());
            $manager->flush();

            $this->addFlash('info', 'survey.create.success');

            return $this->redirectToRoute('app_referent_jecoute_local_surveys_list');
        }

        return $this->render('referent/surveys/create.html.twig', [
            'form' => $form->createView(),
            'suggestedQuestions' => $suggestedQuestionRepository->findAllPublished(),
        ]);
    }

    /**
     * @Route(
     *     path="/jecoute/questionnaire/{uuid}/editer",
     *     name="app_referent_jecoute_local_survey_edit",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET|POST"},
     * )
     *
     * @Security("is_granted('ROLE_REFERENT') and is_granted('IS_AUTHOR_OF', survey)")
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

            return $this->redirectToRoute('app_referent_jecoute_local_surveys_list');
        }

        return $this->render('referent/surveys/create.html.twig', [
            'form' => $form->createView(),
            'suggestedQuestions' => $suggestedQuestionRepository->findAllPublished(),
        ]);
    }

    /**
     * @Route(
     *     path="/jecoute/questionnaire/{uuid}",
     *     name="app_referent_jecoute_national_survey_show",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Security("is_granted('ROLE_REFERENT')")
     *
     * @Entity("nationalSurvey", expr="repository.findOnePublishedByUuid(uuid)")
     */
    public function jecouteNationalSurveyShowAction(NationalSurvey $nationalSurvey): Response
    {
        $form = $this->createForm(
            SurveyFormType::class, $nationalSurvey, ['disabled' => true]
        );

        return $this->render('referent/surveys/show.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *     path="/jecoute/questionnaire/{uuid}/stats",
     *     name="app_referent_jecoute_survey_stats",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Security("is_granted('ROLE_REFERENT')")
     *
     * @Entity("survey", expr="repository.findOneByUuid(uuid)")
     */
    public function jecouteSurveyStatsAction(Survey $survey, StatisticsProvider $provider): Response
    {
        return $this->render('referent/surveys/stats.html.twig', [
            'data' => $provider->getStatsBySurvey($survey),
        ]);
    }

    /**
     * @Route(
     *     path="/jecoute/questionnaire/{uuid}/dupliquer",
     *     name="app_referent_jecoute_local_survey_duplicate",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Entity("survey", expr="repository.findOneByUuid(uuid)")
     *
     * @Security("is_granted('ROLE_REFERENT') and is_granted('IS_AUTHOR_OF', survey)")
     */
    public function jecouteSurveyDuplicateAction(LocalSurvey $survey, ObjectManager $manager): Response
    {
        $clonedSurvey = clone $survey;

        $manager->persist($clonedSurvey);
        $manager->flush();

        $this->addFlash('info', 'survey.duplicate.success');

        return $this->redirectToRoute('app_referent_jecoute_local_surveys_list');
    }

    /**
     * @Route(
     *     path="/jecoute/question/{uuid}/reponses",
     *     name="app_referent_jecoute_survey_stats_answers_list",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"},
     * )
     *
     * @Security("is_granted('ROLE_REFERENT') and is_granted('IS_AUTHOR_OF', surveyQuestion)")
     */
    public function jecouteSurveyAnswersListAction(
        SurveyQuestion $surveyQuestion,
        DataAnswerRepository $dataAnswerRepository
    ): Response {
        return $this->render('referent/surveys/data_answers_dialog_content.html.twig', [
            'answers' => $dataAnswerRepository->findAllBySurveyQuestion($surveyQuestion),
        ]);
    }

    /**
     * @Route(
     *     path="/jecoute/questionnaire/{uuid}/stats/download",
     *     name="app_referent_jecoute_survey_stats_download",
     *     requirements={
     *         "uuid": "%pattern_uuid%",
     *     },
     *     methods={"GET"},
     * )
     *
     * @Entity("survey", expr="repository.findOneByUuid(uuid)")
     *
     * @Security("is_granted('ROLE_REFERENT') and (is_granted('IS_AUTHOR_OF', survey) or survey.isNational())")
     */
    public function jecouteSurveyStatsDownloadAction(Survey $survey, StatisticsExporter $statisticsExporter): Response
    {
        $dataFile = $statisticsExporter->export($survey);

        return new Response($dataFile['content'], Response::HTTP_OK, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment;filename="'.$dataFile['filename'].'"',
        ]);
    }

    /**
     * @Route("/mon-equipe", name="app_referent_organizational_chart", methods={"GET|POST"})
     * @Security("is_granted('ROLE_REFERENT') and is_granted('IS_ROOT_REFERENT')")
     */
    public function organizationalChartAction(
        Request $request,
        OrganizationalChartItemRepository $organizationalChartItemRepository,
        ReferentRepository $referentRepository,
        ObjectManager $manager
    ) {
        $referent = $referentRepository->findOneByEmailAndSelectPersonOrgaChart($this->getUser()->getEmailAddress());

        $form = $this
            ->createForm(PotentialCoReferentsType::class, [
                'referentPersonLinks' => $referent->getReferentPersonLinksWithExistingAdherent(),
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();
        }

        return $this->render('referent/organizational_chart.html.twig', [
            'form' => $form->createView(),
            'organization_chart_items' => $organizationalChartItemRepository->getRootNodes(),
            'referent' => $referent,
        ]);
    }

    /**
     * @Route("/mon-equipe/{id}", name="app_referent_referent_person_link_edit")
     * @Security("is_granted('ROLE_REFERENT') and is_granted('IS_ROOT_REFERENT')")
     */
    public function editReferentPersonLink(
        Request $request,
        ReferentPersonLinkRepository $referentPersonLinkRepository,
        ReferentRepository $referentRepository,
        PersonOrganizationalChartItem $personOrganizationalChartItem
    ) {
        $form = $this->createForm(
            ReferentPersonLinkType::class,
            $referentPersonLinkRepository->findOrCreateByOrgaItemAndReferent(
                $personOrganizationalChartItem,
                $referentRepository->findOneByEmail($this->getUser()->getEmailAddress())
            )
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var ReferentPersonLink $referentPersonLink */
            $referentPersonLink = $form->getData();
            $em = $this->getDoctrine()->getManager();

            $em->persist($referentPersonLink);
            $em->flush();

            $this->addFlash('success', 'Organigramme mis à jour.');

            return $this->redirectToRoute('app_referent_organizational_chart');
        }

        return $this->render('referent/edit_referent_person_link.html.twig', [
            'form_referent_person_link' => $form->createView(),
            'person_organizational_chart_item' => $personOrganizationalChartItem,
        ]);
    }
}

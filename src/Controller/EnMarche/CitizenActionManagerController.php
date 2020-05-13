<?php

namespace App\Controller\EnMarche;

use App\Address\GeoCoder;
use App\CitizenAction\CitizenActionCommand;
use App\CitizenAction\CitizenActionCommandHandler;
use App\CitizenAction\CitizenActionContactParticipantsCommand;
use App\CitizenAction\CitizenActionContactParticipantsCommandHandler;
use App\CitizenAction\CitizenActionManager;
use App\CitizenAction\CitizenActionParticipantsExporter;
use App\CitizenProject\CitizenProjectManager;
use App\Collection\EventRegistrationCollection;
use App\Controller\PrintControllerTrait;
use App\Entity\CitizenAction;
use App\Entity\CitizenProject;
use App\Entity\EventRegistration;
use App\Event\EventCanceledHandler;
use App\Event\EventRegistrationCommand;
use App\Exception\BadUuidRequestException;
use App\Exception\InvalidUuidException;
use App\Form\CitizenActionCommandType;
use App\Form\ContactMembersType;
use App\Repository\CitizenProjectMembershipRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\SnappyResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/projets-citoyens/{project_slug}/actions")
 * @Entity("project", expr="repository.findOneApprovedBySlug(project_slug)")
 * @Entity("action", expr="repository.findOneCitizenActionBySlug(slug)")
 */
class CitizenActionManagerController extends Controller
{
    use PrintControllerTrait;

    private const ACTION_CONTACT = 'contact';
    private const ACTION_EXPORT = 'export';
    private const ACTION_PRINT = 'print';
    private const ACTIONS = [
        self::ACTION_CONTACT,
        self::ACTION_EXPORT,
        self::ACTION_PRINT,
    ];

    /**
     * @Route("/creer", name="app_citizen_action_manager_create", methods={"GET", "POST"})
     * @Security("is_granted('CREATE_CITIZEN_ACTION', project)")
     */
    public function createAction(
        Request $request,
        CitizenProject $project,
        CitizenProjectManager $citizenProjectManager,
        GeoCoder $geoCoder
    ): Response {
        $command = new CitizenActionCommand($this->getUser(), $project);
        $command->setTimeZone($geoCoder->getTimezoneFromIp($request->getClientIp()));
        $form = $this->createForm(CitizenActionCommandType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $action = $this->get(CitizenActionCommandHandler::class)->handle($command);

            $this->get('app.event.registration_handler')->handle(new EventRegistrationCommand($action, $this->getUser()), false);
            $this->addFlash('info', 'citizen_action.creation.success');

            return $this->redirectToRoute('app_citizen_action_show', [
                'slug' => $action->getSlug(),
            ]);
        }

        return $this->render('citizen_action_manager/create.html.twig', [
            'citizen_project' => $project,
            'project_hosts' => $citizenProjectManager->getCitizenProjectAdministrators($project),
            'citizen_action_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/editer", name="app_citizen_action_manager_edit", requirements={"slug": "[A-Za-z0-9\-]+"}, methods={"GET", "POST"})
     * @Security("is_granted('EDIT_CITIZEN_ACTION', project)")
     */
    public function editAction(
        Request $request,
        CitizenProject $project,
        CitizenAction $action,
        CitizenProjectManager $citizenProjectManager
    ): Response {
        $command = CitizenActionCommand::createFromCitizenAction($action);
        $form = $this->createForm(CitizenActionCommandType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $action = $this->get(CitizenActionCommandHandler::class)->handleUpdate($command, $action);

            $this->addFlash('info', 'citizen_action.update.success');

            return $this->redirectToRoute('app_citizen_action_show', [
                'slug' => $action->getSlug(),
            ]);
        }

        return $this->render('citizen_action_manager/edit.html.twig', [
            'citizen_project' => $project,
            'project_hosts' => $citizenProjectManager->getCitizenProjectAdministrators($project),
            'citizen_action_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/annuler", name="app_citizen_action_manager_cancel", methods={"GET", "POST"})
     * @Security("is_granted('CANCEL_CITIZEN_ACTION', project)")
     */
    public function cancelAction(
        Request $request,
        CitizenAction $action,
        CitizenProject $project,
        EventCanceledHandler $eventCanceledHandler
    ): Response {
        $form = $this->createForm(FormType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $eventCanceledHandler->handle($action);
            $this->addFlash('info', 'citizen_action.cancel.success');

            return $this->redirectToRoute('app_citizen_action_show', [
                'slug' => $action->getSlug(),
            ]);
        }

        return $this->render('citizen_action_manager/cancel.html.twig', [
            'citizen_action' => $action,
            'citizen_project' => $project,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/participants/exporter", name="app_citizen_action_export_participants", methods={"POST"})
     * @Security("is_granted('EDIT_CITIZEN_ACTION', project)")
     */
    public function exportParticipantsAction(
        Request $request,
        CitizenAction $citizenAction,
        CitizenProject $project,
        CitizenActionManager $citizenActionManager,
        CitizenProjectMembershipRepository $citizenProjectMembershipRepository
    ): Response {
        $registrations = $this->getRegistrations($request, $citizenAction, self::ACTION_EXPORT);

        if (0 == $registrations->count()) {
            $this->addFlash('error', $this->get('translator')->trans('citizen_action.export.none'));

            return $this->redirectToRoute('app_citizen_action_list_participants', [
                'slug' => $citizenAction->getSlug(),
            ]);
        }

        $participants = $citizenActionManager->populateRegistrationWithAdherentsInformations(
            $registrations,
            $citizenProjectMembershipRepository->findAdministrators($project)
        );
        $exported = $this->get(CitizenActionParticipantsExporter::class)->export($participants);

        return new SnappyResponse($exported, 'inscrits-a-l-action-citoyenne.csv', 'text/csv');
    }

    /**
     * @Route("/{slug}/participants/contacter", name="app_citizen_action_contact_participants", methods={"POST"})
     * @Security("is_granted('EDIT_CITIZEN_ACTION', project)")
     */
    public function contactParticipantsAction(
        Request $request,
        CitizenAction $citizenAction,
        CitizenProject $project,
        CitizenActionManager $citizenActionManager
    ): Response {
        $registrations = $this->getRegistrations($request, $citizenAction, self::ACTION_CONTACT);

        if (0 == $registrations->count()) {
            $this->addFlash('error', $this->get('translator')->trans('citizen_action.export.none'));

            return $this->redirectToRoute('app_citizen_action_list_participants', [
                'slug' => $citizenAction->getSlug(),
            ]);
        }

        $command = new CitizenActionContactParticipantsCommand($this->getUser(), $registrations->toArray());

        $form = $this->createForm(ContactMembersType::class, $command, ['csrf_token_id' => 'citizen_action.contact_participants'])
            ->add('submit', SubmitType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get(CitizenActionContactParticipantsCommandHandler::class)->handle($command);
            $this->addFlash('info', 'citizen_action.contact.success');

            return $this->redirectToRoute('app_citizen_action_list_participants', [
                'slug' => $citizenAction->getSlug(),
            ]);
        }

        $uuids = array_map(function (EventRegistration $registration) {
            return $registration->getUuid()->toString();
        }, $registrations->toArray());

        return $this->render('citizen_action_manager/contact_participants.html.twig', [
            'citizen_action' => $citizenAction,
            'citizen_project' => $project,
            'contacts' => $uuids,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/participants/imprimer", name="app_citizen_action_print_participants", methods={"POST"})
     * @Security("is_granted('EDIT_CITIZEN_ACTION', project)")
     */
    public function printParticipantsAction(
        Request $request,
        CitizenAction $citizenAction,
        CitizenProject $project,
        CitizenActionManager $citizenActionManager,
        CitizenProjectMembershipRepository $citizenProjectMembershipRepository
    ): Response {
        $registrations = $this->getRegistrations($request, $citizenAction, self::ACTION_PRINT);

        if (0 == $registrations->count()) {
            return $this->redirectToRoute('app_citizen_action_list_participants', [
                'slug' => $citizenAction->getSlug(),
            ]);
        }

        return $this->getPdfResponse(
            'citizen_action_manager/print_participants.html.twig',
            [
                'participants' => $citizenActionManager->populateRegistrationWithAdherentsInformations(
                    $registrations,
                    $citizenProjectMembershipRepository->findAdministrators($project)
                ),
            ],
            'Liste des participants.pdf'
        );
    }

    private function getRegistrations(
        Request $request,
        CitizenAction $citizenAction,
        string $action
    ): EventRegistrationCollection {
        if (!\in_array($action, self::ACTIONS)) {
            throw new \InvalidArgumentException("Action '$action' is not allowed.");
        }

        if (!$this->isCsrfTokenValid(sprintf('citizen_action.%s_participants', $action), $request->request->get('token'))) {
            throw $this->createAccessDeniedException("Invalid CSRF protection token to $action members.");
        }

        if (!$uuids = json_decode($request->request->get(sprintf('%ss', $action)), true)) {
            if (self::ACTION_CONTACT === $action) {
                $this->addFlash('info', 'citizen_action.contact.none');
            }

            return new EventRegistrationCollection();
        }

        try {
            $registrations = $this->getDoctrine()->getRepository(EventRegistration::class)->getByEventAndUuid($citizenAction, $uuids);
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        return $registrations;
    }
}

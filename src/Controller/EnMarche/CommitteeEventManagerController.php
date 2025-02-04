<?php

namespace App\Controller\EnMarche;

use App\Entity\Event\Event;
use App\Entity\Event\EventRegistration;
use App\Event\EventCanceledHandler;
use App\Event\EventCommand;
use App\Event\EventCommandHandler;
use App\Event\EventContactMembersCommand;
use App\Event\EventContactMembersCommandHandler;
use App\Event\EventRegistrationExporter;
use App\Exception\BadUuidRequestException;
use App\Exception\InvalidUuidException;
use App\Form\ContactMembersType;
use App\Form\EventCommandType;
use App\Repository\EventRegistrationRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('HOST_EVENT', subject: 'event')]
#[Route(path: '/evenements/{slug}')]
class CommitteeEventManagerController extends AbstractController
{
    private const ACTION_CONTACT = 'contact';
    private const ACTION_EXPORT = 'export';

    private const ACTIONS = [
        self::ACTION_CONTACT,
        self::ACTION_EXPORT,
    ];

    private $eventRegistrationRepository;

    public function __construct(EventRegistrationRepository $eventRegistrationRepository)
    {
        $this->eventRegistrationRepository = $eventRegistrationRepository;
    }

    #[Route(path: '/modifier', name: 'app_committee_event_edit', methods: ['GET', 'POST'])]
    public function editAction(
        Request $request,
        #[MapEntity(expr: 'repository.findOneActiveBySlug(slug)')]
        Event $event,
        EventCommandHandler $handler,
    ): Response {
        $form = $this->createForm(
            EventCommandType::class,
            $command = EventCommand::createFromEvent($event),
            [
                'image_path' => $event->getImagePath(),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->handleUpdate($event, $command);
            $this->addFlash('info', 'event.update.success');

            return $this->redirectToRoute('app_committee_event_show', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('events/edit.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/annuler', name: 'app_committee_event_cancel', methods: ['GET', 'POST'])]
    public function cancelAction(
        Request $request,
        #[MapEntity(expr: 'repository.findOneActiveBySlug(slug)')]
        Event $event,
        EventCanceledHandler $eventCanceledHandler,
    ): Response {
        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventCanceledHandler->handle($event);
            $this->addFlash('info', 'event.cancel.success');

            return $this->redirectToRoute('app_committee_event_show', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('events/cancel.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/inscrits', name: 'app_committee_event_members', methods: ['GET'])]
    public function membersAction(Event $event): Response
    {
        return $this->render('events/members.html.twig', [
            'event' => $event,
            'registrations' => $this->eventRegistrationRepository->findByEvent($event),
        ]);
    }

    #[Route(path: '/inscrits/exporter', name: 'app_committee_event_export_members', methods: ['POST'])]
    public function exportMembersAction(
        Request $request,
        Event $event,
        EventRegistrationExporter $exporter,
    ): Response {
        $registrations = $this->getRegistrations($request, $event, self::ACTION_EXPORT);

        if (!$registrations) {
            return $this->redirectToRoute('app_committee_event_members', [
                'slug' => $event->getSlug(),
            ]);
        }

        $exported = $exporter->export($registrations);

        $response = new Response($exported);
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            'inscrits-a-l-evenement.csv'
        ));

        return $response;
    }

    #[Route(path: '/inscrits/contacter', name: 'app_committee_event_contact_members', methods: ['POST'])]
    public function contactMembersAction(
        Request $request,
        Event $event,
        EventContactMembersCommandHandler $handler,
    ): Response {
        $registrations = $this->getRegistrations($request, $event, self::ACTION_CONTACT);

        if (!$registrations) {
            return $this->redirectToRoute('app_committee_event_members', [
                'slug' => $event->getSlug(),
            ]);
        }

        $command = new EventContactMembersCommand($registrations, $this->getUser());

        $form = $this->createForm(ContactMembersType::class, $command, ['csrf_token_id' => 'event.contact_members'])
            ->add('submit', SubmitType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $handler->handle($command);
            $this->addFlash('info', 'committee.event.contact.success');

            return $this->redirectToRoute('app_committee_event_members', [
                'slug' => $event->getSlug(),
            ]);
        }

        $uuids = array_map(function (EventRegistration $registration) {
            return $registration->getUuid()->toString();
        }, $registrations);

        return $this->render('events/contact_members.html.twig', [
            'event' => $event,
            'contacts' => $uuids,
            'form' => $form->createView(),
        ]);
    }

    private function getRegistrations(Request $request, Event $event, string $action): array
    {
        if (!\in_array($action, self::ACTIONS)) {
            throw new \InvalidArgumentException("Action '$action' is not allowed.");
        }

        if (!$this->isCsrfTokenValid(\sprintf('event.%s_members', $action), $request->request->get('token'))) {
            throw $this->createAccessDeniedException("Invalid CSRF protection token to $action members.");
        }

        if (!$uuids = json_decode($request->request->get(\sprintf('%ss', $action)), true)) {
            if (self::ACTION_CONTACT === $action) {
                $this->addFlash('info', 'committee.event.contact.none');
            }

            return [];
        }

        try {
            $registrations = $this->eventRegistrationRepository->findByEventAndUuid($event, $uuids);
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        return $registrations;
    }
}

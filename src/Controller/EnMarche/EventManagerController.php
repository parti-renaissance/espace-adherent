<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Controller\PrintControllerTrait;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventRegistration;
use AppBundle\Event\EventCanceledHandler;
use AppBundle\Event\EventCommand;
use AppBundle\Event\EventContactMembersCommand;
use AppBundle\Exception\BadUuidRequestException;
use AppBundle\Exception\InvalidUuidException;
use AppBundle\Form\ContactMembersType;
use AppBundle\Form\EventCommandType;
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
 * @Route("/evenements/{slug}")
 * @Security("is_granted('HOST_EVENT', event)")
 */
class EventManagerController extends Controller
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
     * @Route("/modifier", name="app_event_edit", methods={"GET", "POST"})
     * @Entity("event", expr="repository.findOneActiveBySlug(slug)")
     */
    public function editAction(Request $request, Event $event): Response
    {
        $form = $this->createForm(EventCommandType::class, $command = EventCommand::createFromEvent($event));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.event.handler')->handleUpdate($event, $command);
            $this->addFlash('info', 'committee.event.update.success');

            return $this->redirectToRoute('app_event_show', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('events/edit.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/annuler", name="app_event_cancel", methods={"GET", "POST"})
     * @Entity("event", expr="repository.findOneActiveBySlug(slug)")
     */
    public function cancelAction(Request $request, Event $event, EventCanceledHandler $eventCanceledHandler): Response
    {
        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventCanceledHandler->handle($event);
            $this->addFlash('info', 'committee.event.cancel.success');

            return $this->redirectToRoute('app_event_show', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('events/cancel.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/inscrits", name="app_event_members", methods={"GET"})
     */
    public function membersAction(Event $event): Response
    {
        $registrations = $this->getDoctrine()->getRepository(EventRegistration::class)->findByEvent($event);

        return $this->render('events/members.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
            'registrations' => $registrations,
        ]);
    }

    /**
     * @Route("/inscrits/exporter", name="app_event_export_members", methods={"POST"})
     */
    public function exportMembersAction(Request $request, Event $event): Response
    {
        $registrations = $this->getRegistrations($request, $event, self::ACTION_EXPORT);

        if (!$registrations) {
            return $this->redirectToRoute('app_event_members', [
                'slug' => $event->getSlug(),
            ]);
        }

        $exported = $this->get('app.event.registration_exporter')->export($registrations);

        return new SnappyResponse($exported, 'inscrits-a-l-evenement.csv', 'text/csv');
    }

    /**
     * @Route("/inscrits/contacter", name="app_event_contact_members", methods={"POST"})
     */
    public function contactMembersAction(Request $request, Event $event): Response
    {
        $registrations = $this->getRegistrations($request, $event, self::ACTION_CONTACT);

        if (!$registrations) {
            return $this->redirectToRoute('app_event_members', [
                'slug' => $event->getSlug(),
            ]);
        }

        $command = new EventContactMembersCommand($registrations, $this->getUser());

        $form = $this->createForm(ContactMembersType::class, $command, ['csrf_token_id' => 'event.contact_members'])
            ->add('submit', SubmitType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.event.contact_members_handler')->handle($command);
            $this->addFlash('info', 'committee.event.contact.success');

            return $this->redirectToRoute('app_event_members', [
                'slug' => $event->getSlug(),
            ]);
        }

        $uuids = array_map(function (EventRegistration $registration) {
            return $registration->getUuid()->toString();
        }, $registrations);

        return $this->render('events/contact_members.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
            'contacts' => $uuids,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/inscrits/imprimer", name="app_event_print_members", methods={"POST"})
     */
    public function printMembersAction(Request $request, Event $event): Response
    {
        $registrations = $this->getRegistrations($request, $event, self::ACTION_PRINT);

        if (!$registrations) {
            return $this->redirectToRoute('app_event_members', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->getPdfResponse(
            'events/print_members.html.twig',
            [
                'registrations' => $registrations,
            ],
            'Liste des participants.pdf'
        );
    }

    private function getRegistrations(Request $request, Event $event, string $action): array
    {
        if (!\in_array($action, self::ACTIONS)) {
            throw new \InvalidArgumentException("Action '$action' is not allowed.");
        }

        if (!$this->isCsrfTokenValid(sprintf('event.%s_members', $action), $request->request->get('token'))) {
            throw $this->createAccessDeniedException("Invalid CSRF protection token to $action members.");
        }

        if (!$uuids = json_decode($request->request->get(sprintf('%ss', $action)), true)) {
            if (self::ACTION_CONTACT === $action) {
                $this->addFlash('info', 'committee.event.contact.none');
            }

            return [];
        }

        try {
            $registrations = $this->getDoctrine()->getRepository(EventRegistration::class)->findByEventAndUuid($event, $uuids);
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        return $registrations;
    }
}

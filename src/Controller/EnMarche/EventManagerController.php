<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\EventRegistration;
use AppBundle\Event\EventCommand;
use AppBundle\Event\EventContactMembersCommand;
use AppBundle\Entity\Event;
use AppBundle\Exception\BadUuidRequestException;
use AppBundle\Exception\InvalidUuidException;
use AppBundle\Form\ContactMembersType;
use AppBundle\Form\EventCommandType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/evenements/{slug}")
 * @Security("is_granted('HOST_EVENT', event)")
 */
class EventManagerController extends Controller
{
    /**
     * @Route("/modifier", name="app_event_edit")
     * @Method("GET|POST")
     * @Entity("event", expr="repository.findOneActiveBySlug(slug)")
     */
    public function editAction(Request $request, Event $event): Response
    {
        $form = $this->createForm(EventCommandType::class, $command = EventCommand::createFromEvent($event));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.event.handler')->handleUpdate($event, $command);
            $this->addFlash('info', $this->get('translator')->trans('committee.event.update.success'));

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
     * @Route("/annuler", name="app_event_cancel")
     * @Method("GET|POST")
     * @Entity("event", expr="repository.findOneActiveBySlug(slug)")
     */
    public function cancelAction(Request $request, Event $event): Response
    {
        $command = EventCommand::createFromEvent($event);

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.event.handler')->handleCancel($event, $command);
            $this->addFlash('info', $this->get('translator')->trans('committee.event.cancel.success'));

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
     * @Route("/inscrits", name="app_event_members")
     * @Method("GET")
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
     * @Route("/inscrits/exporter", name="app_event_export_members")
     * @Method("POST")
     */
    public function exportMembersAction(Request $request, Event $event): Response
    {
        if (!$this->isCsrfTokenValid('event.export_members', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to export members.');
        }

        $uuids = json_decode($request->request->get('exports'), true);

        if (!$uuids) {
            return $this->redirectToRoute('app_event_members', [
                'slug' => $event->getSlug(),
            ]);
        }

        $repository = $this->getDoctrine()->getRepository(EventRegistration::class);

        try {
            $registrations = $repository->findByUuidAndEvent($event, $uuids);
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        if (!$registrations) {
            return $this->redirectToRoute('app_event_members', [
                'slug' => $event->getSlug(),
            ]);
        }

        $exported = $this->get('app.event.registration_exporter')->export($registrations);

        return new Response($exported, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inscrits-a-l-evenement.csv"',
        ]);
    }

    /**
     * @Route("/inscrits/contacter", name="app_event_contact_members")
     * @Method("POST")
     */
    public function contactMembersAction(Request $request, Event $event): Response
    {
        if (!$this->isCsrfTokenValid('event.contact_members', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to contact members.');
        }

        $uuids = json_decode($request->request->get('contacts', '[]'), true);

        if (!$uuids) {
            $this->addFlash('info', $this->get('translator')->trans('committee.event.contact.none'));

            return $this->redirectToRoute('app_event_members', [
                'slug' => $event->getSlug(),
            ]);
        }

        $repository = $this->getDoctrine()->getRepository(EventRegistration::class);

        try {
            $registrations = $repository->findByUuidAndEvent($event, $uuids);
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        $command = new EventContactMembersCommand($registrations, $this->getUser());

        $form = $this->createForm(ContactMembersType::class, $command, ['csrf_token_id' => 'event.contact_members'])
            ->add('submit', SubmitType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.event.contact_members_handler')->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('committee.event.contact.success'));

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
}

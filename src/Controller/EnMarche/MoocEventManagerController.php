<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\EventRegistration;
use AppBundle\Entity\MoocEvent;
use AppBundle\Event\EventContactMembersCommand;
use AppBundle\Exception\BadUuidRequestException;
use AppBundle\Exception\InvalidUuidException;
use AppBundle\Form\ContactMembersType;
use AppBundle\Form\MoocEventType;
use AppBundle\MoocEvent\MoocEventCommand;
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
 * @Route("/evenement-mooc/{slug}")
 * @Security("is_granted('EDIT_MOOC_EVENT', moocEvent)")
 * @Entity("moocEvent", expr="repository.findOnePublishedBySlug(slug)")
 */
class MoocEventManagerController extends Controller
{
    /**
     * @Route("/modifier", name="app_mooc_event_edit")
     * @Method("GET|POST")
     */
    public function editAction(Request $request, MoocEvent $moocEvent): Response
    {
        $form = $this->createForm(MoocEventType::class, $command = MoocEventCommand::createFromMoocEvent($moocEvent));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.mooc_event.handler')->handleUpdate($moocEvent, $command);
            $this->addFlash('info', $this->get('translator')->trans('mooc_event.update.success'));

            return $this->redirectToRoute('app_mooc_event_show', [
                'slug' => $moocEvent->getSlug(),
            ]);
        }

        return $this->render('mooc_event/edit.html.twig', [
            'mooc_event' => $moocEvent,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/annuler", name="app_mooc_event_cancel")
     * @Method("GET|POST")
     */
    public function cancelAction(Request $request, MoocEvent $moocEvent): Response
    {
        $command = MoocEventCommand::createFromMoocEvent($moocEvent);

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.mooc_event.handler')->handleCancel($moocEvent, $command);
            $this->addFlash('info', $this->get('translator')->trans('mooc_event.cancel.success'));

            return $this->redirectToRoute('app_mooc_event_show', [
                'slug' => $moocEvent->getSlug(),
            ]);
        }

        return $this->render('mooc_event/cancel.html.twig', [
            'mooc_event' => $moocEvent,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/inscrits", name="app_mooc_event_members")
     * @Method("GET")
     */
    public function membersAction(MoocEvent $moocEvent): Response
    {
        $registrations = $this->getDoctrine()->getRepository(EventRegistration::class)->findByEvent($moocEvent);

        return $this->render('mooc_event/members.html.twig', [
            'mooc_event' => $moocEvent,
            'registrations' => $registrations,
        ]);
    }

    /**
     * @Route("/inscrits/exporter", name="app_mooc_event_export_members")
     * @Method("POST")
     */
    public function exportMembersAction(Request $request, MoocEvent $moocEvent): Response
    {
        if (!$this->isCsrfTokenValid('event.export_members', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to export members.');
        }

        $uuids = json_decode($request->request->get('exports'), true);

        if (!$uuids) {
            return $this->redirectToRoute('app_mooc_event_members', [
                'slug' => $moocEvent->getSlug(),
            ]);
        }

        $repository = $this->getDoctrine()->getRepository(EventRegistration::class);

        try {
            $registrations = $repository->findByUuidAndEvent($moocEvent, $uuids);
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        if (!$registrations) {
            return $this->redirectToRoute('app_mooc_event_members', [
                'slug' => $moocEvent->getSlug(),
            ]);
        }

        $exported = $this->get('app.event.registration_exporter')->export($registrations);

        return new Response($exported, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inscrits-a-l-evenement.csv"',
        ]);
    }

    /**
     * @Route("/inscrits/contacter", name="app_mooc_event_contact_members")
     * @Method("POST")
     */
    public function contactMembersAction(Request $request, MoocEvent $moocEvent): Response
    {
        if (!$this->isCsrfTokenValid('event.contact_members', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to contact members.');
        }

        $uuids = json_decode($request->request->get('contacts', '[]'), true);

        if (!$uuids) {
            $this->addFlash('info', $this->get('translator')->trans('mooc_event.contact.none'));

            return $this->redirectToRoute('app_mooc_event_members', [
                'slug' => $moocEvent->getSlug(),
            ]);
        }

        $repository = $this->getDoctrine()->getRepository(EventRegistration::class);

        try {
            $registrations = $repository->findByUuidAndEvent($moocEvent, $uuids);
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        $command = new EventContactMembersCommand($registrations, $this->getUser());

        $form = $this->createForm(ContactMembersType::class, $command, ['csrf_token_id' => 'event.contact_members'])
            ->add('submit', SubmitType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.event.contact_members_handler')->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('mooc_event.contact.success'));

            return $this->redirectToRoute('app_mooc_event_members', [
                'slug' => $moocEvent->getSlug(),
            ]);
        }

        $uuids = array_map(function (EventRegistration $registration) {
            return $registration->getUuid()->toString();
        }, $registrations);

        return $this->render('mooc_event/contact_members.html.twig', [
            'mooc_event' => $moocEvent,
            'contacts' => $uuids,
            'form' => $form->createView(),
        ]);
    }
}

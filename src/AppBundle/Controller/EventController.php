<?php

namespace AppBundle\Controller;

use AppBundle\Committee\CommitteePermissions;
use AppBundle\Event\EventCommand;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Entity\Event;
use AppBundle\Form\EventCommandType;
use AppBundle\Form\EventRegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/evenements/{uuid}/{slug}", requirements={"uuid": "%pattern_uuid%"})
 */
class EventController extends Controller
{
    /**
     * @Route("", name="app_committee_show_event")
     * @Method("GET")
     */
    public function showAction(Event $event): Response
    {
        return $this->render('events/show.html.twig', [
            'event' => $event,
            'committee' => $event->getCommittee(),
        ]);
    }

    /**
     * @Route("/modifier", name="app_event_edit")
     * @Method("GET|POST")
     */
    public function editAction(Request $request, Event $event): Response
    {
        $user = $this->getUser();
        $organizer = $event->getOrganizer();
        $committee = $event->getCommittee();

        $authorized = $organizer && $user && $user->getId() === $organizer;
        if (!$authorized) {
            $authorized = $committee && $this->isGranted(CommitteePermissions::HOST, $committee);
        }

        if (!$authorized) {
            throw $this->createAccessDeniedException();
        }

        $command = EventCommand::createFromEvent($event);

        $form = $this->createForm(EventCommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.event.handler')->handleUpdate($event, $command);
            $this->addFlash('info', $this->get('translator')->trans('committee.event.update.success'));

            return $this->redirectToRoute('app_committee_show_event', [
                'uuid' => (string) $event->getUuid(),
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('events/edit.html.twig', [
            'event' => $event,
            'committee' => $committee,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/inscription", name="app_committee_attend_event")
     * @Method("GET|POST")
     */
    public function attendAction(Request $request, Event $event): Response
    {
        $committee = $event->getCommittee();

        $command = new EventRegistrationCommand($event, $this->getUser());
        $form = $this->createForm(EventRegistrationType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.event.registration_handler')->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('committee.event.registration.success'));

            return $this->redirectToRoute('app_committee_attend_event_confirmation', [
                'uuid' => (string) $event->getUuid(),
                'slug' => $event->getSlug(),
                'registration' => (string) $command->getRegistrationUuid(),
            ]);
        }

        return $this->render('events/attend.html.twig', [
            'committee_event' => $event,
            'committee' => $committee,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *   path="/confirmation",
     *   name="app_committee_attend_event_confirmation",
     *   condition="request.query.has('registration')"
     * )
     * @Method("GET")
     */
    public function attendConfirmationAction(Request $request, Event $event): Response
    {
        $manager = $this->get('app.event.registration_manager');

        if (!$registration = $manager->findRegistration($uuid = $request->query->get('registration'))) {
            throw $this->createNotFoundException(sprintf('Unable to find event registration by its UUID: %s', $uuid));
        }

        if (!$registration->matches($event, $this->getUser())) {
            throw $this->createAccessDeniedException('Invalid event registration');
        }

        return $this->render('events/attend_confirmation.html.twig', [
            'committee_event' => $event,
            'committee' => $event->getCommittee(),
            'registration' => $registration,
        ]);
    }
}

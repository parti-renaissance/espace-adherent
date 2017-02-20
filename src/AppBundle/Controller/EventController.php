<?php

namespace AppBundle\Controller;

use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Entity\Event;
use AppBundle\Form\EventRegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/comites/{committee_uuid}/evenements/{slug}", requirements={
 *   "committee_uuid": "%pattern_uuid%"
 * })
 */
class EventController extends Controller
{
    /**
     * @Route("", name="app_committee_show_event")
     * @Method("GET")
     * @Entity("event", expr="repository.findOneBySlug(slug)")
     */
    public function showAction(Event $event): Response
    {
        return $this->render('events/show.html.twig', [
            'committee_event' => $event,
            'committee' => $event->getCommittee(),
        ]);
    }

    /**
     * @Route("/inscription", name="app_committee_attend_event")
     * @Method("GET|POST")
     * @Entity("event", expr="repository.findOneBySlug(slug)")
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
                'committee_uuid' => (string) $committee->getUuid(),
                'slug' => $event->getSlug(),
                'uuid' => (string) $command->getRegistrationUuid(),
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
     *   condition="request.query.has('uuid')"
     * )
     * @Method("GET")
     * @Entity("event", expr="repository.findOneBySlug(slug)")
     */
    public function attendConfirmationAction(Request $request, Event $event): Response
    {
        $manager = $this->get('app.event.registration_manager');

        if (!$registration = $manager->findRegistration($uuid = $request->query->get('uuid'))) {
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

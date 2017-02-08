<?php

namespace AppBundle\Controller;

use AppBundle\Committee\Event\CommitteeEventRegistrationCommand;
use AppBundle\Entity\CommitteeEvent;
use AppBundle\Form\CommitteeEventRegistrationType;
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
class CommitteeEventController extends Controller
{
    /**
     * @Route("", name="app_committee_show_event")
     * @Method("GET")
     * @Entity("event", expr="repository.findOneBySlug(slug)")
     */
    public function showAction(CommitteeEvent $event): Response
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
    public function attendAction(Request $request, CommitteeEvent $event): Response
    {
        $committee = $event->getCommittee();

        $command = new CommitteeEventRegistrationCommand($event, $this->getUser());
        $form = $this->createForm(CommitteeEventRegistrationType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.committee.committee_event_registration_handler')->handle($command);
            $this->addFlash('notice', $this->get('translator')->trans('committee.event.registration.success'));

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
    public function attendConfirmationAction(Request $request, CommitteeEvent $event): Response
    {
        $manager = $this->get('app.committee.event_registration_manager');

        if (!$registration = $manager->findRegistration($uuid = $request->query->get('uuid'))) {
            throw $this->createNotFoundException(sprintf('Unable to find event registration by its UUID: %s', $uuid));
        }

        if (!$registration->matches($event, $this->getUser())) {
            die('loool');
            throw $this->createAccessDeniedException('Invalid event registration');
        }

        return $this->render('events/attend_confirmation.html.twig', [
            'committee_event' => $event,
            'committee' => $event->getCommittee(),
            'registration' => $registration,
        ]);
    }
}

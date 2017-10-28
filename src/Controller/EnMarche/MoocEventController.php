<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\MoocEvent;
use AppBundle\Event\EventInvitation;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Form\EventInvitationType;
use AppBundle\Form\EventRegistrationType;
use AppBundle\Form\MoocEventType;
use AppBundle\MoocEvent\MoocEventCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/evenement-mooc")
 * @Entity("moocEvent", expr="repository.findOnePublishedBySlug(slug)")
 */
class MoocEventController extends Controller
{
    /**
     * @Route("/creer", name="app_create_mooc_event")
     * @Method("GET|POST")
     */
    public function createMoocEventAction(Request $request): Response
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('page_campus');
        }

        $command = new MoocEventCommand($this->getUser());
        $form = $this->createForm(MoocEventType::class, $command)->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.mooc_event.handler')->handle($command);

            $registrationCommand = new EventRegistrationCommand($command->getMoocEvent(), $this->getUser());
            $this->get('app.mooc_event.registration_handler')->handle($registrationCommand);

            $this->addFlash('info', 'mooc_event.creation.success');

            return $this->redirectToRoute('app_create_mooc_event');
        }

        return $this->render('mooc_event/add.html.twig', [
            'mooc_event' => $command,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}", name="app_mooc_event_show")
     * @Method("GET")
     */
    public function showAction(MoocEvent $moocEvent): Response
    {
        return $this->render('mooc_event/show.html.twig', [
            'mooc_event' => $moocEvent,
        ]);
    }

    /**
     * @Route("/{slug}/invitation", name="app_mooc_event_invite")
     * @Method("GET|POST")
     */
    public function inviteAction(Request $request, MoocEvent $moocEvent): Response
    {
        $eventInvitation = EventInvitation::createFromAdherent($this->getUser());

        $form = $this->createForm(EventInvitationType::class, $eventInvitation)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var EventInvitation $invitation */
            $invitation = $form->getData();

            $this->get('app.mooc_event.invitation_handler')->handle($invitation, $moocEvent);
            $request->getSession()->set('mooc_event_invitations_count', count($invitation->guests));

            return $this->redirectToRoute('app_mooc_event_invitation_sent', [
                'slug' => $moocEvent->getSlug(),
            ]);
        }

        return $this->render('mooc_event/invitation.html.twig', [
            'mooc_event' => $moocEvent,
            'invitation_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/ical", name="app_mooc_event_export_ical")
     * @Method("GET")
     */
    public function exportIcalAction(MoocEvent $moocEvent): Response
    {
        $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT.'; filename='.$moocEvent->getSlug().'.ics';

        $response = new Response($this->get('jms_serializer')->serialize($moocEvent, 'ical'));
        $response->headers->set('Content-Type', 'text/calendar');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @Route("/{slug}/inscription", name="app_mooc_event_attend")
     * @Method("GET|POST")
     */
    public function attendAction(Request $request, MoocEvent $moocEvent): Response
    {
        if ($moocEvent->isFinished()) {
            throw $this->createNotFoundException(sprintf('Event "%s" is finished and does not accept registrations anymore', $moocEvent->getUuid()));
        }

        if ($moocEvent->isCancelled()) {
            throw $this->createNotFoundException(sprintf('Event "%s" is cancelled and does not accept registrations anymore', $moocEvent->getUuid()));
        }

        $command = new EventRegistrationCommand($moocEvent, $this->getUser());
        $form = $this->createForm(EventRegistrationType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.mooc_event.registration_handler')->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('mooc_event.registration.success'));

            return $this->redirectToRoute('app_mooc_event_attend_confirmation', [
                'slug' => $moocEvent->getSlug(),
                'registration' => (string) $command->getRegistrationUuid(),
            ]);
        }

        return $this->render('mooc_event/attend.html.twig', [
            'mooc_event' => $moocEvent,
            'form' => $form->createView(),
        ]);
    }
}

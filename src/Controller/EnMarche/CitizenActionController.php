<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\CitizenAction\CitizenActionManager;
use AppBundle\CitizenAction\CitizenActionRegistrationCommandHandler;
use AppBundle\Controller\CanaryControllerTrait;
use AppBundle\Controller\EntityControllerTrait;
use AppBundle\Entity\CitizenAction;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Exception\BadUuidRequestException;
use AppBundle\Exception\InvalidUuidException;
use AppBundle\Form\EventRegistrationType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

/**
 * @Route("/action-citoyenne")
 * @Entity("action", expr="repository.findOneCitizenActionBySlug(slug)")
 */
class CitizenActionController extends Controller
{
    use EntityControllerTrait;
    use CanaryControllerTrait;

    /**
     * @Route("/{slug}", name="app_citizen_action_show")
     * @Method("GET")
     */
    public function showAction(CitizenAction $action, CitizenActionManager $citizenActionManager): Response
    {
        return $this->render('citizen_action/show.html.twig', [
            'citizen_action' => $action,
            'participants' => $citizenActionManager->getRegistrations($action),
        ]);
    }

    /**
     * @Route("/{slug}/inscription", name="app_citizen_action_attend")
     * @Method("GET|POST")
     */
    public function attendAction(Request $request, CitizenAction $citizenAction): Response
    {
        if ($citizenAction->isFinished()) {
            throw $this->createNotFoundException(sprintf('Event "%s" is finished and does not accept registrations anymore', $citizenAction->getUuid()));
        }

        if ($citizenAction->isCancelled()) {
            throw $this->createNotFoundException(sprintf('Event "%s" is cancelled and does not accept registrations anymore', $citizenAction->getUuid()));
        }

        $command = new EventRegistrationCommand($citizenAction, $this->getUser());
        $form = $this->createForm(EventRegistrationType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get(CitizenActionRegistrationCommandHandler::class)->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('citizen_initiative.registration.success'));

            return $this->redirectToRoute('app_citizen_action_attend_confirmation', [
                'slug' => $citizenAction->getSlug(),
                'registration' => (string) $command->getRegistrationUuid(),
            ]);
        }

        return $this->render('citizen_action/attend.html.twig', [
            'citizen_action' => $citizenAction,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route(
     *   path="/{slug}/confirmation",
     *   name="app_citizen_action_attend_confirmation",
     *   condition="request.query.has('registration')"
     * )
     * @Method("GET")
     */
    public function attendConfirmationAction(Request $request, CitizenAction $citizenAction): Response
    {
        $manager = $this->get('app.event.registration_manager');

        try {
            if (!$registration = $manager->findRegistration($uuid = $request->query->get('registration'))) {
                throw $this->createNotFoundException(sprintf('Unable to find event registration by its UUID: %s', $uuid));
            }
        } catch (InvalidUuidException $e) {
            throw new BadUuidRequestException($e);
        }

        if (!$registration->matches($citizenAction, $this->getUser())) {
            throw $this->createAccessDeniedException('Invalid event registration');
        }

        return $this->render('citizen_action/attend_confirmation.html.twig', [
            'citizen_action' => $citizenAction,
            'registration' => $registration,
        ]);
    }

    /**
     * @Route("/{slug}/ical", name="app_citizen_action_export_ical")
     * @Method("GET")
     */
    public function exportIcalAction(CitizenAction $citizenAction): Response
    {
        $disposition = sprintf('%s; filename=%s.ics', ResponseHeaderBag::DISPOSITION_ATTACHMENT, $citizenAction->getSlug());
        $response = new Response($this->get('jms_serializer')->serialize($citizenAction, 'ical'));
        $response->headers->set('Content-Type', 'text/calendar');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    /**
     * @Route("/{slug}/participants", name="app_citizen_action_list_participants")
     * @Method("GET")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function listParticipantsAction(CitizenAction $citizenAction, CitizenActionManager $citizenActionManager): Response
    {
        $this->disableInProduction();

        $registrations = $citizenActionManager->populateRegistrationWithAdherentsInformations($citizenActionManager->getRegistrations($citizenAction));

        return $this->render('citizen_action/list_participants.html.twig', [
            'citizen_action' => $citizenAction,
            'participants' => $registrations,
        ]);
    }
}

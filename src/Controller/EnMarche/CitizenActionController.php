<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\CitizenAction\CitizenActionManager;
use AppBundle\CitizenAction\CitizenActionRegistrationCommandHandler;
use AppBundle\Controller\EntityControllerTrait;
use AppBundle\Entity\CitizenAction;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Exception\BadUuidRequestException;
use AppBundle\Exception\InvalidUuidException;
use AppBundle\Form\EventRegistrationType;
use AppBundle\Repository\CitizenProjectMembershipRepository;
use AppBundle\Security\Http\Session\AnonymousFollowerSession;
use Doctrine\ORM\EntityNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/action-citoyenne")
 * @Entity("action", expr="repository.findOneCitizenActionBySlug(slug)")
 */
class CitizenActionController extends Controller
{
    use EntityControllerTrait;

    /**
     * @Route("/{slug}", name="app_citizen_action_show", methods={"GET"})
     */
    public function showAction(CitizenAction $action, CitizenActionManager $citizenActionManager): Response
    {
        return $this->render('citizen_action/show.html.twig', [
            'citizen_action' => $action,
            'participants' => $citizenActionManager->getRegistrations($action),
        ]);
    }

    /**
     * @Route("/{slug}/inscription", name="app_citizen_action_attend", methods={"GET", "POST"})
     */
    public function attendAction(Request $request, CitizenAction $citizenAction, ?UserInterface $adherent): Response
    {
        if ($citizenAction->isFinished()) {
            throw $this->createNotFoundException(sprintf('CitizenAction "%s" is finished and does not accept registrations anymore', $citizenAction->getUuid()));
        }

        if ($citizenAction->isCancelled()) {
            throw $this->createNotFoundException(sprintf('CitizenAction "%s" is cancelled and does not accept registrations anymore', $citizenAction->getUuid()));
        }

        if ($citizenAction->isFull()) {
            $this->addFlash('info', 'L\'événement est complet');

            return $this->redirectToRoute('app_citizen_action_show', ['slug' => $citizenAction->getSlug()]);
        }

        if (
            $this->isGranted('IS_ANONYMOUS')
            && $authenticate = $this->get(AnonymousFollowerSession::class)->start($request)
        ) {
            return $authenticate;
        }

        $command = new EventRegistrationCommand($citizenAction, $adherent);

        $form = $this
            ->createForm(EventRegistrationType::class, $command)
            ->handleRequest($request)
        ;

        if ($adherent || ($form->isSubmitted() && $form->isValid())) {
            $this->get(CitizenActionRegistrationCommandHandler::class)->handle($command);
            $this->addFlash('info', 'citizen_action.registration.success');

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
     * @Route("/{slug}/desinscription", name="app_citizen_action_unregistration", condition="request.isXmlHttpRequest()", methods={"GET", "POST"})
     * @Security("is_granted('UNREGISTER_CITIZEN_ACTION', citizenAction)")
     */
    public function unregistrationAction(Request $request, CitizenAction $citizenAction): JsonResponse
    {
        if (!$this->isCsrfTokenValid('event.unregistration', $token = $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to unregister from the citizen action.');
        }

        try {
            $this->get(CitizenActionManager::class)->unregisterFromCitizenAction($citizenAction, $this->getUser());
        } catch (EntityNotFoundException $e) {
            return new JsonResponse(
                ['error' => 'Impossible d\'exécuter la désinscription de l\'action citoyenne, votre inscription n\'est pas trouvée.'],
                Response::HTTP_NOT_FOUND
            );
        }

        return new JsonResponse();
    }

    /**
     * @Route(
     *     path="/{slug}/confirmation",
     *     name="app_citizen_action_attend_confirmation",
     *     condition="request.query.has('registration')",
     *     methods={"GET"}
     * )
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
     * @Route("/{slug}/ical", name="app_citizen_action_export_ical", methods={"GET"})
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
     * @Route("/{slug}/participants", name="app_citizen_action_list_participants", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function listParticipantsAction(
        CitizenAction $citizenAction,
        CitizenActionManager $citizenActionManager,
        CitizenProjectMembershipRepository $citizenProjectMembershipRepository
    ): Response {
        $participants = $citizenActionManager->populateRegistrationWithAdherentsInformations(
            $citizenActionManager->getRegistrations($citizenAction),
            $citizenProjectMembershipRepository->findAdministrators($citizenAction->getCitizenProject())
        );

        return $this->render('citizen_action/list_participants.html.twig', [
            'citizen_action' => $citizenAction,
            'participants' => $participants,
        ]);
    }
}

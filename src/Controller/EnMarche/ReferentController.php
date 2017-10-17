<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Entity\Projection\ReferentManagedUser;
use AppBundle\Event\EventCommand;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Form\EventCommandType;
use AppBundle\Form\ReferentMessageType;
use AppBundle\Referent\ManagedUsersFilter;
use AppBundle\Referent\ReferentMessage;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/espace-referent")
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentController extends Controller
{
    const TOKEN_ID = 'referent_managed_users';

    /**
     * @Route("/utilisateurs", name="app_referent_users")
     * @Method("GET")
     */
    public function usersAction(Request $request): Response
    {
        $filter = new ManagedUsersFilter();
        $filter->handleRequest($request);

        if ($filter->hasToken() && !$this->isCsrfTokenValid(self::TOKEN_ID, $filter->getToken())) {
            return $this->redirectToRoute('app_referent_users');
        }

        $repository = $this->getDoctrine()->getRepository(ReferentManagedUser::class);
        $results = $repository->search($this->getUser(), $filter->hasToken() ? $filter : null, false);

        $filter->setToken($this->get('security.csrf.token_manager')->getToken(self::TOKEN_ID));

        return $this->render('referent/users_list.html.twig', [
            'filter' => $filter,
            'has_filter' => $request->query->has(ManagedUsersFilter::PARAMETER_TOKEN),
            'results_count' => $results->count(),
            'results' => $results->getQuery()->getResult(),
        ]);
    }

    /**
     * @Route("/utilisateurs/message", name="app_referent_users_message")
     * @Method("GET|POST")
     */
    public function usersSendMessageAction(Request $request): Response
    {
        $filter = new ManagedUsersFilter();
        $filter->handleRequest($request);

        if ($filter->hasToken() && !$this->isCsrfTokenValid(self::TOKEN_ID, $filter->getToken())) {
            return $this->redirectToRoute('app_referent_users');
        }

        $message = new ReferentMessage($this->getUser(), $filter);

        $form = $this->createForm(ReferentMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('old_sound_rabbit_mq.referent_message_dispatcher_producer')->scheduleDispatch($message);
            $this->addFlash('info', $this->get('translator')->trans('referent.message.success'));

            return $this->redirect($this->generateUrl('app_referent_users').$filter);
        }

        $repository = $this->getDoctrine()->getRepository(ReferentManagedUser::class);
        $results = $repository->search($this->getUser(), $filter->hasToken() ? $filter : null, true);

        return $this->render('referent/users_message.html.twig', [
            'filter' => $filter,
            'results_count' => $results->count(),
            'message' => $message,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/evenements", name="app_referent_events")
     * @Method("GET")
     */
    public function eventsAction(): Response
    {
        $list = $this->getDoctrine()->getRepository(Event::class)->findManagedBy($this->getUser());
        $exporter = $this->get('app.referent.managed_events.exporter');

        return $this->render('referent/events_list.html.twig', [
            'managedEventsJson' => $exporter->exportAsJson($list),
        ]);
    }

    /**
     * @Route("/evenements/creer", name="app_referent_events_create")
     * @Method("GET|POST")
     */
    public function eventsCreateAction(Request $request): Response
    {
        $command = new EventCommand($this->getUser());
        $form = $this->createForm(EventCommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('app.event.handler')->handle($command);

            $registrationCommand = new EventRegistrationCommand($event, $this->getUser());
            $this->get('app.event.registration_handler')->handle($registrationCommand);

            $this->addFlash('info', $this->get('translator')->trans('referent.event.creation.success'));

            return $this->redirectToRoute('app_event_show', [
                'slug' => (string) $command->getEvent()->getSlug(),
            ]);
        }

        return $this->render('referent/event_create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/comites", name="app_referent_commitees")
     * @Method("GET")
     */
    public function commiteesAction(): Response
    {
        $list = $this->getDoctrine()->getRepository(Committee::class)->findManagedBy($this->getUser());
        $exporter = $this->get('app.referent.managed_committees.exporter');

        return $this->render('referent/committees_list.html.twig', [
            'managedCommitteesJson' => $exporter->exportAsJson($list),
        ]);
    }
}

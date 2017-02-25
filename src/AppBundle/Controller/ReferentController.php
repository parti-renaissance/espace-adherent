<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Form\ReferentMessageType;
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
    /**
     * @Route("/utilisateurs", name="app_referent_users")
     * @Method("GET")
     */
    public function usersAction(): Response
    {
        $factory = $this->get('app.referent.managed_users.factory');
        $list = $factory->createManagedUsersCollectionFor($this->getUser());
        $exporter = $this->get('app.referent.managed_users.exporter');

        return $this->render('referent/users/list-all.html.twig', [
            'managedUsersJson' => $exporter->exportAsJson($list),
        ]);
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/marcheurs", name="app_referent_subscribers")
     * @Method("GET")
     */
    public function usersSendMessageSubscribersAction(): Response
    {
        $factory = $this->get('app.referent.managed_users.factory');
        $list = $factory->createManagedSubscribersCollectionFor($this->getUser());
        $exporter = $this->get('app.referent.managed_users.exporter');

        return $this->render('referent/users/list-subscribers.html.twig', [
            'managedUsersJson' => $exporter->exportAsJson($list),
        ]);
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/adherents", name="app_referent_adherents")
     * @Method("GET")
     */
    public function usersSendMessageAdherentsAction(): Response
    {
        $factory = $this->get('app.referent.managed_users.factory');
        $list = $factory->createManagedAdherentsCollectionFor($this->getUser());
        $exporter = $this->get('app.referent.managed_users.exporter');

        return $this->render('referent/users/list-adherents.html.twig', [
            'managedUsersJson' => $exporter->exportAsJson($list),
        ]);
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/non-membres-comites", name="app_referent_nonfollowers")
     * @Method("GET|POST")
     */
    public function usersSendMessageNonFollowersAction(): Response
    {
        $factory = $this->get('app.referent.managed_users.factory');
        $list = $factory->createManagedNonFollowersCollectionFor($this->getUser());
        $exporter = $this->get('app.referent.managed_users.exporter');

        return $this->render('referent/users/list-nonfollowers.html.twig', [
            'managedUsersJson' => $exporter->exportAsJson($list),
        ]);
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/membres-comites", name="app_referent_followers")
     * @Method("GET|POST")
     */
    public function usersSendMessageFollowersAction(): Response
    {
        $factory = $this->get('app.referent.managed_users.factory');
        $list = $factory->createManagedFollowersCollectionFor($this->getUser());
        $exporter = $this->get('app.referent.managed_users.exporter');

        return $this->render('referent/users/list-followers.html.twig', [
            'managedUsersJson' => $exporter->exportAsJson($list),
        ]);
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/animateurs-comites", name="app_referent_hosts")
     * @Method("GET|POST")
     */
    public function usersSendMessageHostsAction(): Response
    {
        $factory = $this->get('app.referent.managed_users.factory');
        $list = $factory->createManagedHostsCollectionFor($this->getUser());
        $exporter = $this->get('app.referent.managed_users.exporter');

        return $this->render('referent/users/list-hosts.html.twig', [
            'managedUsersJson' => $exporter->exportAsJson($list),
        ]);
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/selectionnes", name="app_referent_users_selected")
     * @Method("POST")
     */
    public function usersSendMessageSelectedAction(Request $request): Response
    {
        $from = $request->query->get('from', 'users');
        $from = in_array($from, ['subscribers', 'adherents', 'followers', 'nonfollowers', 'hosts']) ? $from : 'users';

        try {
            $selected = \GuzzleHttp\json_decode($request->request->get('selected_users_json', '[]'), true);
        } catch (\InvalidArgumentException $e) {
            $selected = [];
        }

        if (empty($selected)) {
            return $this->redirectToRoute('app_referent_'.$from);
        }

        $factory = $this->get('app.referent.message_factory');
        $referentMessage = $factory->createReferentMessageFor($this->getUser(), $selected);

        if (empty($referentMessage->getTo())) {
            return $this->redirectToRoute('app_referent_users');
        }

        $form = $this->createForm(ReferentMessageType::class, $referentMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.referent.message_handler')->handle($referentMessage);
            $this->addFlash('info', $this->get('translator')->trans('referent.message.success'));

            return $this->redirectToRoute('app_referent_'.$from);
        }

        return $this->render('referent/users/message.html.twig', [
            'selected' => $selected,
            'referentMessage' => $referentMessage,
            'form' => $form->createView(),
            'from' => $from,
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

        return $this->render('referent/events/list.html.twig', [
            'managedEventsJson' => $exporter->exportAsJson($list),
        ]);
    }

    /**
     * @Route("/evenements/creer", name="app_referent_events_create")
     * @Method("GET|POST")
     */
    public function eventsCreateAction(): Response
    {
        // TODO IMPLEMENT
        return $this->render('referent/events/create.html.twig');
    }

    /**
     * @Route("/comites", name="app_referent_commitees")
     * @Method("GET")
     */
    public function commiteesAction(): Response
    {
        $list = $this->getDoctrine()->getRepository(Committee::class)->findManagedBy($this->getUser());
        $exporter = $this->get('app.referent.managed_committees.exporter');

        return $this->render('referent/commitees/list.html.twig', [
            'managedCommitteesJson' => $exporter->exportAsJson($list),
        ]);
    }
}

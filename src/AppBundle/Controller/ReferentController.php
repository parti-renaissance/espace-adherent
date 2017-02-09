<?php

namespace AppBundle\Controller;

use AppBundle\Form\ReferentMessageType;
use AppBundle\Entity\Committee;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/referent")
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
        $factory = $this->get('app.referent.data_grid_factory');
        $exporter = $this->get('app.referent.managed_users.exporter');

        return $this->render('referent/users/list.html.twig', [
            'managedUsersJson' => $exporter->exportAsJson($factory->findUsersManagedBy($this->getUser())),
        ]);
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/selectionnes", name="app_referent_users_selected")
     * @Method("POST")
     */
    public function usersSendMessageSelectedAction(Request $request): Response
    {
        $selected = $request->request->get('selected', []);

        if (empty($selected)) {
            return $this->redirectToRoute('app_referent_users');
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

            return $this->redirectToRoute('app_referent_users');
        }

        return $this->render('referent/users/message-selected.html.twig', [
            'selected' => $selected,
            'referentMessage' => $referentMessage,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/marcheurs", name="app_referent_users_subscribers")
     * @Method("GET")
     */
    public function usersSendMessageSubscribersAction(): Response
    {
        // TODO IMPLEMENT
        return $this->render('referent/users/message-subscribers.html.twig');
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/adherents", name="app_referent_users_adherents")
     * @Method("GET")
     */
    public function usersSendMessageAdherentsAction(): Response
    {
        // TODO IMPLEMENT
        return $this->render('referent/users/message-adherents.html.twig');
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/membres-comites", name="app_referent_users_followers")
     * @Method("GET|POST")
     */
    public function usersSendMessageFollowersAction(): Response
    {
        // TODO IMPLEMENT
        return $this->render('referent/users/message-followers.html.twig');
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/animateurs-comites", name="app_referent_users_hosts")
     * @Method("GET|POST")
     */
    public function usersSendMessageHostsAction(): Response
    {
        // TODO IMPLEMENT
        return $this->render('referent/users/message-hosts.html.twig');
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/code-postal", name="app_referent_users_postal_code")
     * @Method("GET|POST")
     */
    public function usersSendMessagePostalCodeAction(): Response
    {
        // TODO IMPLEMENT
        return $this->render('referent/users/message-postal-code.html.twig');
    }

    /**
     * @Route("/evenements", name="app_referent_events")
     * @Method("GET")
     */
    public function eventsAction(): Response
    {
        // TODO IMPLEMENT
        return $this->render('referent/events/list.html.twig');
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
        $factory = $this->get('app.referent.data_grid_factory');
        $exporter = $this->get('app.referent.managed_committees.exporter');

        return $this->render('referent/commitees/list.html.twig', [
            'commiteesAsJson' => $exporter->exportAsJson($factory->findCommitteesManagedBy($this->getUser())),
        ]);
    }
}

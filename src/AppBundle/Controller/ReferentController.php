<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Event\EventCommand;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Form\EventCommandType;
use AppBundle\Form\ReferentMessageType;
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
    /**
     * @Route("/utilisateurs", name="app_referent_users")
     * @Method("GET")
     */
    public function usersAction(): Response
    {
        return $this->render('referent/users/list-all.html.twig', [
            'managedUsersJson' => $this->readDumpedUsersList('all') ?? '[]',
        ]);
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/marcheurs", name="app_referent_subscribers")
     * @Method("GET")
     */
    public function usersSendMessageSubscribersAction(): Response
    {
        return $this->render('referent/users/list-subscribers.html.twig', [
            'managedUsersJson' => $this->readDumpedUsersList('subscribers') ?? '[]',
        ]);
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/adherents", name="app_referent_adherents")
     * @Method("GET")
     */
    public function usersSendMessageAdherentsAction(): Response
    {
        return $this->render('referent/users/list-adherents.html.twig', [
            'managedUsersJson' => $this->readDumpedUsersList('adherents') ?? '[]',
        ]);
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/non-membres-comites", name="app_referent_nonfollowers")
     * @Method("GET|POST")
     */
    public function usersSendMessageNonFollowersAction(): Response
    {
        return $this->render('referent/users/list-nonfollowers.html.twig', [
            'managedUsersJson' => $this->readDumpedUsersList('non_followers') ?? '[]',
        ]);
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/membres-comites", name="app_referent_followers")
     * @Method("GET|POST")
     */
    public function usersSendMessageFollowersAction(): Response
    {
        return $this->render('referent/users/list-followers.html.twig', [
            'managedUsersJson' => $this->readDumpedUsersList('followers') ?? '[]',
        ]);
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/animateurs-comites", name="app_referent_hosts")
     * @Method("GET|POST")
     */
    public function usersSendMessageHostsAction(): Response
    {
        return $this->render('referent/users/list-hosts.html.twig', [
            'managedUsersJson' => $this->readDumpedUsersList('hosts') ?? '[]',
        ]);
    }

    /**
     * @Route("/utilisateurs/envoyer-un-message/selectionnes", name="app_referent_users_selected")
     * @Method("POST")
     */
    public function usersSendMessageSelectedAction(Request $request): Response
    {
        $from = $request->query->get('from', 'users');
        $from = in_array($from, ['subscribers', 'adherents', 'followers', 'nonfollowers', 'hosts'], true) ? $from : 'users';
        $uuid = $this->getUser()->getUuid()->toString();
        $selected = $request->request->get('selected_users_json');

        $dbReader = $this->get('app.referent.dumped_database_reader');

        $allowedSelectedUsers = $dbReader->filterAllowedUsers($uuid, $selected);
        if (empty($allowedSelectedUsers)) {
            return $this->redirectToRoute('app_referent_'.$from);
        }

        $referentMessage = new ReferentMessage($this->getUser(), $allowedSelectedUsers);

        $form = $this->createForm(ReferentMessageType::class, $referentMessage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.referent.message_handler')->handle($referentMessage);
            $this->addFlash('info', $this->get('translator')->trans('referent.message.success'));

            return $this->redirectToRoute('app_referent_'.$from);
        }

        return $this->render('referent/users/message.html.twig', [
            'selected' => $dbReader->serializeSelected($allowedSelectedUsers),
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

            return $this->redirectToRoute('app_committee_show_event', [
                'uuid' => (string) $command->getEvent()->getUuid(),
                'slug' => (string) $command->getEvent()->getSlug(),
            ]);
        }

        return $this->render('referent/events/create.html.twig', [
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

        return $this->render('referent/commitees/list.html.twig', [
            'managedCommitteesJson' => $exporter->exportAsJson($list),
        ]);
    }

    private function readDumpedUsersList(string $type)
    {
        return $this->get('app.referent.dumped_database_reader')->readList(
            $this->getUser()->getUuid()->toString(),
            $type
        );
    }
}

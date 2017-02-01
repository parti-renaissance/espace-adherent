<?php

namespace AppBundle\Controller;

use AppBundle\Committee\Event\CommitteeEventCommand;
use AppBundle\Entity\Committee;
use AppBundle\Form\CommitteeEventCommandType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/comites/{uuid}/{slug}", requirements={"uuid": "%pattern_uuid%"})
 */
class CommitteeController extends Controller
{
    /**
     * @Route(name="app_committee_show")
     * @Method("GET")
     * @Security("is_granted('SHOW_COMMITTEE', committee)")
     */
    public function showAction(Committee $committee): Response
    {
        $committeeManager = $this->get('app.committee_manager');

        return $this->render('committee/show.html.twig', [
            'committee' => $committee,
            'committee_members_count' => $committeeManager->getMembersCount($committee),
            'committee_hosts' => $committeeManager->findCommitteeHostsList($committee),
        ]);
    }

    /**
     * @Route("/editer", name="app_committee_edit")
     * @Method("GET|POST")
     * @Security("is_granted('HOST_COMMITTEE', committee)")
     */
    public function editAction(Committee $committee): Response
    {
        return new Response('TO BE IMPLEMENTED');
    }

    /**
     * @Route("/evenements/ajouter", name="app_committee_add_event")
     * @Method("GET|POST")
     * @Security("is_granted('HOST_COMMITTEE', committee)")
     */
    public function addEventAction(Request $request, Committee $committee): Response
    {
        $command = new CommitteeEventCommand($this->getUser(), $committee);
        $form = $this->createForm(CommitteeEventCommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.committee.committee_event_handler')->handle($command);
            $this->addFlash('info', $this->get('translator')->trans('committee.event.creation.success'));

            return $this->redirectToRoute('app_committee_show_event', [
                'committee_uuid' => (string) $committee->getUuid(),
                'slug' => (string) $command->getCommitteeEvent()->getSlug(),
            ]);
        }

        return $this->render('committee/add_event.html.twig', [
            'committee' => $committee,
            'committee_hosts' => $this->get('app.committee_manager')->findCommitteeHostsList($committee),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/membres", name="app_commitee_list_members")
     * @Method("GET")
     * @Security("is_granted('HOST_COMMITTEE', committee)")
     */
    public function listMembersAction(Committee $committee): Response
    {
        return new Response('TO BE IMPLEMENTED');
    }

    /**
     * @Route("/rejoindre", name="app_committee_follow")
     * @Method("POST")
     * @Security("is_granted('FOLLOW_COMMITTEE', committee)")
     */
    public function followAction(Committee $committee): Response
    {
        return new Response('FOLLOWING COMMITTEE');
    }

    /**
     * @Route("/quitter", name="app_committee_unfollow")
     * @Method("POST")
     * @Security("is_granted('UNFOLLOW_COMMITTEE', committee)")
     */
    public function unfollowAction(Committee $committee): Response
    {
        return new Response('UNFOLLOWING COMMITTEE');
    }
}

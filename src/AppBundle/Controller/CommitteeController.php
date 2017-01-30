<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Committee;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/comites")
 */
class CommitteeController extends Controller
{
    /**
     * @Route("/{uuid}/{slug}", name="app_committee_show", requirements={
     *   "uuid": "%pattern_uuid%"
     * })
     * @Method("GET")
     * @Security("is_granted('SHOW_COMMITTEE', committee)")
     */
    public function showAction(Committee $committee): Response
    {
        return $this->render('committee/show.html.twig', [
            'committee' => $committee,
        ]);
    }

    /**
     * @Route("/{uuid}/evenements/ajouter", name="app_committee_add_event", requirements={
     *   "uuid": "%pattern_uuid%"
     * })
     * @Method("GET|POST")
     * @Security("is_granted('HOST_COMMITTEE', committee)")
     */
    public function addEventAction(Committee $committee): Response
    {
        return $this->render('committee/add_event.html.twig', [
            'committee' => $committee,
            'committee_hosts' => $this->get('app.committee_manager')->findCommitteeHostsList($committee),
        ]);
    }

    /**
     * @Route("/{uuid}/{slug}/membres", name="app_commitee_list_members", requirements={
     *   "uuid": "%pattern_uuid%"
     * })
     * @Method("GET")
     * @Security("is_granted('HOST_COMMITTEE', committee)")
     */
    public function listMembersAction(Committee $committee): Response
    {
        return new Response('TO BE IMPLEMENTED');
    }

    /**
     * @Route("/{uuid}/{slug}/rejoindre", name="app_committee_follow", requirements={
     *   "uuid": "%pattern_uuid%"
     * })
     * @Method("POST")
     * @Security("is_granted('FOLLOW_COMMITTEE', committee)")
     */
    public function followAction(Committee $committee): Response
    {
        return new Response('FOLLOWING COMMITTEE');
    }

    /**
     * @Route("/{uuid}/{slug}/quitter", name="app_committee_unfollow", requirements={
     *   "uuid": "%pattern_uuid%"
     * })
     * @Method("POST")
     * @Security("is_granted('LEAVE_COMMITTEE', committee)")
     */
    public function unfollowAction(Committee $committee): Response
    {
        return new Response('UNFOLLOWING COMMITTEE');
    }
}

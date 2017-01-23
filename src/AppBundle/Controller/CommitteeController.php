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
     * @Route("/comites/{uuid}/{slug}", name="app_committee_show")
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
     * @Route("/comites/{uuid}/{slug}/rejoindre", name="app_committee_follow")
     * @Method("POST")
     * @Security("is_granted('FOLLOW_COMMITTEE', committee)")
     */
    public function followAction(Committee $committee): Response
    {
        return new Response('FOLLOWING COMMITTEE');
    }

    /**
     * @Route("/comites/{uuid}/{slug}/quitter", name="app_committee_unfollow")
     * @Method("POST")
     * @Security("is_granted('LEAVE_COMMITTEE', committee)")
     */
    public function unfollowAction(Committee $committee): Response
    {
        return new Response('UNFOLLOWING COMMITTEE');
    }
}

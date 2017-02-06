<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CommitteeEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/comites/{committee_uuid}/evenements/{slug}", requirements={
 *   "committee_uuid": "%pattern_uuid%"
 * })
 */
class CommitteeEventController extends Controller
{
    /**
     * @Route("", name="app_committee_show_event")
     * @Method("GET")
     * @Entity("event", expr="repository.findOneBySlug(slug)")
     */
    public function showAction(CommitteeEvent $event): Response
    {
        return $this->render('events/show.html.twig', [
            'committee_event' => $event,
            'committee' => $event->getCommittee(),
        ]);
    }

    /**
     * @Route("/participer", name="app_committee_attend_event")
     * @Method("GET")
     */
    public function attendAction(): Response
    {
        return new Response('TO BE IMPLEMENTED');
    }
}

<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegacyController extends Controller
{
    /**
     * @Route("/espaceperso/evenement/{id}-{slug}", requirements={"id": "\d+"})
     * @Method("GET")
     * @Entity("event", expr="repository.find(id)")
     */
    public function redirectEventAction(Event $event): Response
    {
        return $this->redirectToRoute('app_event_show', [
            'slug' => $event->getSlug(),
        ], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route("/espaceperso/comite/{id}-{slug}", requirements={"id": "\d+"})
     * @Method("GET")
     * @Entity("committee", expr="repository.find(id)")
     */
    public function redirectCommitteeAction(Committee $committee): Response
    {
        return $this->redirectToRoute('app_committee_show', [
            'slug' => $committee->getSlug(),
        ], Response::HTTP_MOVED_PERMANENTLY);
    }
}

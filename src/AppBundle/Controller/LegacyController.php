<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class LegacyController extends Controller
{
    /**
     * @Route("/espaceperso/evenement/{id}-{slug}", requirements={"id"="\d+"})
     * @Method("GET")
     * @Entity("event", expr="repository.find(id)")
     */
    public function redirectEventAction(Event $event): Response
    {
        return $this->redirectToRoute('app_committee_show_event', [
            'uuid' => $event->getUuid()->toString(),
            'slug' => $event->getSlug(),
        ], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route("/espaceperso/comite/{id}-{slug}", requirements={"id"="\d+"})
     * @Method("GET")
     * @Entity("committee", expr="repository.find(id)")
     */
    public function redirectCommitteeAction(Committee $committee): Response
    {
        return $this->redirectToRoute('app_committee_show', [
            'uuid' => $committee->getUuid()->toString(),
            'slug' => $committee->getSlug(),
        ], Response::HTTP_MOVED_PERMANENTLY);
    }
}

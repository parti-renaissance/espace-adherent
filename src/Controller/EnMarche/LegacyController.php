<?php

namespace App\Controller\EnMarche;

use App\Entity\Committee;
use App\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegacyController extends Controller
{
    /**
     * @Route("/espaceperso/evenement/{id}-{slug}", requirements={"id": "\d+"}, methods={"GET"})
     * @Entity("event", expr="repository.find(id)")
     */
    public function redirectEventAction(Event $event): Response
    {
        return $this->redirectToRoute('app_event_show', [
            'slug' => $event->getSlug(),
        ], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route("/espaceperso/comite/{id}-{slug}", requirements={"id": "\d+"}, methods={"GET"})
     * @Entity("committee", expr="repository.find(id)")
     */
    public function redirectCommitteeAction(Committee $committee): Response
    {
        return $this->redirectToRoute('app_committee_show', [
            'slug' => $committee->getSlug(),
        ], Response::HTTP_MOVED_PERMANENTLY);
    }
}

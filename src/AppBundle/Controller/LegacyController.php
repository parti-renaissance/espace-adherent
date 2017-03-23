<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class LegacyController extends Controller
{
    /**
     * @Route("/espaceperso/evenement/{id}-{slug}", requirements={"id"="\d+"})
     * @Method("GET")
     */
    public function redirectEventAction($id): Response
    {
        $event = $this->getDoctrine()->getRepository(Event::class)->find((int) $id);

        if (!$event) {
            throw $this->createNotFoundException();
        }

        return $this->redirectToRoute('app_committee_show_event', [
            'uuid' => $event->getUuid()->toString(),
            'slug' => $event->getSlug(),
        ], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * @Route("/espaceperso/comite/{id}-{slug}", requirements={"id"="\d+"})
     * @Method("GET")
     */
    public function redirectCommitteeAction($id): Response
    {
        $committee = $this->getDoctrine()->getRepository(Committee::class)->find((int) $id);

        if (!$committee) {
            throw $this->createNotFoundException();
        }

        return $this->redirectToRoute('app_committee_show', [
            'uuid' => $committee->getUuid()->toString(),
            'slug' => $committee->getSlug(),
        ], Response::HTTP_MOVED_PERMANENTLY);
    }
}

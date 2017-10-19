<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\Event;
use AppBundle\Entity\EventCategory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class MapController extends Controller
{
    /**
     * @Route("/le-mouvement/la-carte", name="map_committees")
     * @Method("GET")
     */
    public function committeesAction()
    {
        $doctrine = $this->getDoctrine();

        return $this->render('map/committees.html.twig', [
            'userCount' => $doctrine->getRepository(Adherent::class)->count(),
            'eventCount' => $doctrine->getRepository(Event::class)->count(),
            'committeeCount' => $doctrine->getRepository(Committee::class)->count(),
        ]);
    }

    // Unlike the other actions of this class the route of this action is defined in the config to prevent an override.
    public function eventsAction()
    {
        $doctrine = $this->getDoctrine();

        return $this->render('map/events.html.twig', [
            'eventCount' => $doctrine->getRepository(Event::class)->countUpcomingEvents(),
            'categories' => $doctrine->getRepository(EventCategory::class)->findBy([], ['name' => 'ASC']),
        ]);
    }
}

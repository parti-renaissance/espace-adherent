<?php

namespace App\Controller\EnMarche;

use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Event\CommitteeEvent;
use App\Repository\EventCategoryRepository;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MapController extends AbstractController
{
    /**
     * @Route("/le-mouvement/la-carte", name="map_committees", methods={"GET"})
     */
    public function committeesAction()
    {
        $doctrine = $this->getDoctrine();

        return $this->render('map/committees.html.twig', [
            'userCount' => $doctrine->getRepository(Adherent::class)->countAdherents(),
            'eventCount' => $doctrine->getRepository(CommitteeEvent::class)
                ->countElements(true, $this->getUser() instanceof Adherent),
            'committeeCount' => $doctrine->getRepository(Committee::class)->countElements(),
        ]);
    }

    // Unlike the other actions of this class the route of this action is defined in the config to prevent another route to match his path
    public function eventsAction(EventRepository $eventRepository, EventCategoryRepository $eventCategoryRepository)
    {
        return $this->render('map/events.html.twig', [
            'eventCount' => $eventRepository->countUpcomingEvents($this->getUser() instanceof Adherent),
            'categories' => $eventCategoryRepository->findAllEnabledOrderedByName(),
        ]);
    }
}

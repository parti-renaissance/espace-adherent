<?php

namespace App\Controller\Legislatives;

use App\Entity\Adherent;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MapsController extends AbstractController
{
    /**
     * @Route("/la-carte", name="legislatives_map", methods={"GET"})
     */
    public function candidatesAction(): Response
    {
        return $this->render('legislatives/map_candidates.html.twig');
    }

    /**
     * @Route("/les-evenements", name="legislatives_events", methods={"GET"})
     */
    public function eventsAction(EventRepository $repository): Response
    {
        return $this->render('legislatives/map_events.html.twig', [
            'eventCount' => $repository->countUpcomingEvents($this->getUser() instanceof Adherent),
        ]);
    }
}

<?php

namespace App\Controller\EnMarche;

use App\Entity\Committee;
use App\Entity\Event\CommitteeEvent;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegacyController extends AbstractController
{
    #[Route(path: '/espaceperso/evenement/{id}-{slug}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function redirectEventAction(
        #[MapEntity(expr: 'repository.find(id)')]
        CommitteeEvent $event,
    ): Response {
        return $this->redirectToRoute('app_committee_event_show', [
            'slug' => $event->getSlug(),
        ], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route(path: '/espaceperso/comite/{id}-{slug}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function redirectCommitteeAction(
        #[MapEntity(expr: 'repository.find(id)')]
        Committee $committee,
    ): Response {
        return $this->redirectToRoute('app_committee_show', [
            'slug' => $committee->getSlug(),
        ], Response::HTTP_MOVED_PERMANENTLY);
    }
}

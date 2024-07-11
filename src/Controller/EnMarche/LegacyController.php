<?php

namespace App\Controller\EnMarche;

use App\Entity\Committee;
use App\Entity\Event\CommitteeEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LegacyController extends AbstractController
{
    #[Entity('event', expr: 'repository.find(id)')]
    #[Route(path: '/espaceperso/evenement/{id}-{slug}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function redirectEventAction(CommitteeEvent $event): Response
    {
        return $this->redirectToRoute('app_committee_event_show', [
            'slug' => $event->getSlug(),
        ], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Entity('committee', expr: 'repository.find(id)')]
    #[Route(path: '/espaceperso/comite/{id}-{slug}', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function redirectCommitteeAction(Committee $committee): Response
    {
        return $this->redirectToRoute('app_committee_show', [
            'slug' => $committee->getSlug(),
        ], Response::HTTP_MOVED_PERMANENTLY);
    }
}

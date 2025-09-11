<?php

namespace App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Repository\AdherentRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{slug}/{uuid}', name: 'app_national_event_my_inscription', requirements: ['slug' => '[^/]+', 'uuid' => '%pattern_uuid%'], methods: ['GET'])]
class MyInscriptionController extends AbstractController
{
    public function __invoke(
        #[MapEntity(mapping: ['slug' => 'slug'])] NationalEvent $event,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] EventInscription $inscription,
        AdherentRepository $adherentRepository,
    ): Response {
        if ($inscription->event !== $event || $event->endDate < new \DateTimeImmutable('+3 days')) {
            throw $this->createNotFoundException('Inscription not found for this event.');
        }

        return $this->render('renaissance/national_event/my_inscription.html.twig', [
            'event' => $event,
            'inscription' => $inscription,
            'roommate' => $inscription->roommateIdentifier ? $adherentRepository->findByPublicId($inscription->roommateIdentifier, true) : null,
        ]);
    }
}

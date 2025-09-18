<?php

namespace App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\PublicId\AdherentPublicIdGenerator;
use App\PublicId\MeetingInscriptionPublicIdGenerator;
use App\Repository\AdherentRepository;
use App\Repository\NationalEvent\EventInscriptionRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{slug}/{uuid}', name: 'app_national_event_my_inscription', requirements: ['slug' => '[^/]+', 'uuid' => '%pattern_uuid%'], methods: ['GET'])]
class MyInscriptionController extends AbstractController
{
    public function __construct(private readonly EventInscriptionRepository $eventInscriptionRepository)
    {
    }

    public function __invoke(
        #[MapEntity(mapping: ['slug' => 'slug'])] NationalEvent $event,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] EventInscription $inscription,
        AdherentRepository $adherentRepository,
    ): Response {
        if ($inscription->event !== $event) {
            throw $this->createNotFoundException('Inscription not found for this event.');
        }

        if ($inscription->roommateIdentifier) {
            if (
                preg_match(AdherentPublicIdGenerator::REGEX, $inscription->roommateIdentifier)
                && ($roommateAdherent = $adherentRepository->findByPublicId($inscription->roommateIdentifier, true))
            ) {
                $roommateData = [
                    'public_id' => $roommateAdherent->getPublicId(),
                    'first_name' => $roommateAdherent->getFirstName(),
                    'last_name' => $roommateAdherent->getLastName(),
                ];
            } elseif (
                preg_match(MeetingInscriptionPublicIdGenerator::REGEX, $inscription->roommateIdentifier)
                && ($roommateInscription = $this->eventInscriptionRepository->findByPublicId($inscription->roommateIdentifier))
            ) {
                $roommateData = [
                    'public_id' => $roommateInscription->getPublicId(),
                    'first_name' => $roommateInscription->firstName,
                    'last_name' => $roommateInscription->lastName,
                ];
            }
        }

        return $this->render('renaissance/national_event/my_inscription.html.twig', [
            'event' => $event,
            'inscription' => $inscription,
            'roommate' => $roommateData ?? null,
        ]);
    }
}

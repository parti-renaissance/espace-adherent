<?php

namespace App\Controller\Renaissance\NationalEvent;

use App\Repository\NationalEvent\NationalEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/confirmation', name: 'app_renaissance_national_event_inscription_confirmation', methods: ['GET'])]
class EventInscriptionConfirmationController extends AbstractController
{
    public function __invoke(NationalEventRepository $nationalEventRepository): Response
    {
        if (!$event = $nationalEventRepository->findOneForInscriptions()) {
            return $this->redirectToRoute('renaissance_site');
        }

        return $this->render('renaissance/national_event/event_inscription_confirmation.html.twig', [
            'event' => $event,
        ]);
    }
}

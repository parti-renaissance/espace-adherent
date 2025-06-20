<?php

namespace App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\NationalEvent;
use App\Repository\NationalEvent\NationalEventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{slug}/confirmation', name: 'app_national_event_inscription_confirmation', requirements: ['slug' => '[^/]+'], methods: ['GET'])]
class EventInscriptionConfirmationController extends AbstractController
{
    public function __invoke(NationalEventRepository $nationalEventRepository, NationalEvent $event): Response
    {
        return $this->render('renaissance/national_event/confirmation/'.$event->type->value.'.html.twig', [
            'event' => $event,
        ]);
    }
}

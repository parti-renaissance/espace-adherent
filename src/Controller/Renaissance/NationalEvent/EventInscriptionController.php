<?php

namespace App\Controller\Renaissance\NationalEvent;

use App\Form\NationalEvent\EventInscriptionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/', name: 'app_renaissance_national_event_index', methods: ['GET', 'POST'])]
#[Route('/inscription', name: 'app_renaissance_national_event_inscription', methods: ['GET', 'POST'])]
class EventInscriptionController extends AbstractController
{
    public function __invoke(Request $request): Response
    {
        $form = $this->createForm(EventInscriptionType::class)->handleRequest($request);

        return $this->renderForm('renaissance/national_event/event_inscription.html.twig', [
            'form' => $form,
        ]);
    }
}

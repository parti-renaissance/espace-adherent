<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Form\ConfirmActionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{uuid}/confirmer', name: 'app_national_event_confirm_inscription', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET', 'POST'])]
class ConfirmInscriptionController extends AbstractController
{
    public function __invoke(Request $request, EntityManagerInterface $entityManager, EventInscription $inscription): Response
    {
        $form = $this
            ->createForm(ConfirmActionType::class, null, ['with_deny' => false])
            ->handleRequest($request)
        ;

        if ($isConfirmed = $form->isSubmitted()) {
            if (!$inscription->confirmedAt) {
                $inscription->confirmedAt = new \DateTime();
                $entityManager->flush();
            }
        }

        return $this->render('renaissance/national_event/confirm_inscription.html.twig', [
            'form' => $form->createView(),
            'event' => $inscription->event,
            'inscription' => $inscription,
            'is_confirmed' => $isConfirmed,
        ]);
    }
}

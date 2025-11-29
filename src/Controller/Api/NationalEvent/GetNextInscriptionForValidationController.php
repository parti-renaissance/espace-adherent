<?php

declare(strict_types=1);

namespace App\Controller\Api\NationalEvent;

use App\Repository\NationalEvent\EventInscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetNextInscriptionForValidationController extends AbstractController
{
    public function __invoke(Request $request, EntityManagerInterface $entityManager, EventInscriptionRepository $inscriptionRepository): Response
    {
        if (!$eventInscription = $inscriptionRepository->findNextToValidate($request->query->getInt('event'))) {
            return $this->json(null, Response::HTTP_NO_CONTENT);
        }

        $eventInscription->markAsInValidation();
        $entityManager->flush();

        return $this->json($eventInscription, context: ['groups' => ['event_inscription_read_for_validation']]);
    }
}

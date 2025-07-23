<?php

namespace App\Controller\Api\AdherentMessage;

use App\Entity\AdherentMessage\AdherentMessage;
use App\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GetAdherentMessageRecipientsCountController extends AbstractController
{
    public function __invoke(Request $request, AdherentMessage $message, AdherentRepository $adherentRepository): Response
    {
        return $this->json(
            array_merge(
                [
                    'contacts' => $adherentRepository->countAdherentsForMessage($message, byEmail: true, byPush: true, asUnion: true),
                    'total' => $adherentRepository->countAdherentsForMessage($message),
                ],
                $request->query->getBoolean('partial') ? [] : [
                    'push' => $adherentRepository->countAdherentsForMessage($message, byPush: true),
                    'email' => $adherentRepository->countAdherentsForMessage($message, byEmail: true),
                    'push_email' => $adherentRepository->countAdherentsForMessage($message, byEmail: true, byPush: true),
                    'only_push' => $adherentRepository->countAdherentsForMessage($message, byEmail: false, byPush: true),
                    'only_email' => $adherentRepository->countAdherentsForMessage($message, byEmail: true, byPush: false),
                ]
            )
        );
    }
}

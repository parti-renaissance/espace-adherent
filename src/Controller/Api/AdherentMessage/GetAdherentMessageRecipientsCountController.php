<?php

namespace App\Controller\Api\AdherentMessage;

use App\Entity\AdherentMessage\AbstractAdherentMessage;
use App\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class GetAdherentMessageRecipientsCountController extends AbstractController
{
    public function __invoke(AbstractAdherentMessage $message, AdherentRepository $adherentRepository): Response
    {
        return $this->json(
            [
                'push' => $adherentRepository->countAdherentsForMessage($message, byPush: true),
                'email' => $message->getRecipientCount() ?: $adherentRepository->countAdherentsForMessage($message, byEmail: true),
                'push_email' => $adherentRepository->countAdherentsForMessage($message, byEmail: true, byPush: true),
                'only_push' => $adherentRepository->countAdherentsForMessage($message, byEmail: false, byPush: true),
                'only_email' => $message->getRecipientCount() ?: $adherentRepository->countAdherentsForMessage($message, byEmail: true, byPush: false),
                'contacts' => $adherentRepository->countAdherentsForMessage($message, byEmail: true, byPush: true, asUnion: true),
                'total' => $adherentRepository->countAdherentsForMessage($message),
            ],
        );
    }
}

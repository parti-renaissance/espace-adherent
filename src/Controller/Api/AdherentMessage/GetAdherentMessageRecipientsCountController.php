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
                'push' => $adherentRepository->countAdherentsForMessage($message, false, true),
                'email' => $message->getRecipientCount() ?: $adherentRepository->countAdherentsForMessage($message, true, false),
                'push_email' => $adherentRepository->countAdherentsForMessage($message, true, true),
                'in_app' => $adherentRepository->countAdherentsForMessage($message, null, null, true),
            ],
        );
    }
}

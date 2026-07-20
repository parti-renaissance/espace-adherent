<?php

declare(strict_types=1);

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
        $total = $adherentRepository->countAdherentsForMessage($message);

        if ($request->query->getBoolean('partial')) {
            return $this->json([
                'contacts' => $adherentRepository->countAdherentsForMessage($message, byEmail: true, byPush: true, asUnion: true),
                'total' => $total,
            ]);
        }

        $push = $adherentRepository->countAdherentsForMessage($message, byPush: true);
        $email = $adherentRepository->countAdherentsForMessage($message, byEmail: true);
        $pushEmail = $adherentRepository->countAdherentsForMessage($message, byEmail: true, byPush: true);

        return $this->json([
            'contacts' => $push + $email - $pushEmail,
            'total' => $total,
            'push' => $push,
            'email' => $email,
            'push_email' => $pushEmail,
            'only_push' => $adherentRepository->countAdherentsForMessage($message, byEmail: false, byPush: true),
            'only_email' => $email - $pushEmail,
        ]);
    }
}

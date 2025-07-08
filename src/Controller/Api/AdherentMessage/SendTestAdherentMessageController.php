<?php

namespace App\Controller\Api\AdherentMessage;

use App\AdherentMessage\AdherentMessageManager;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class SendTestAdherentMessageController extends AbstractController
{
    public function __invoke(AdherentMessageManager $manager, AdherentMessage $message, #[CurrentUser] Adherent $user): Response
    {
        if ($manager->sendTest($message, $user)) {
            return $this->json('OK');
        }

        return $this->json('Une erreur inconnue est survenue', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}

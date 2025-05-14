<?php

namespace App\Controller\Api\Agora;

use App\Agora\AgoraMembershipHandler;
use App\Entity\Adherent;
use App\Entity\Agora;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class JoinAgoraController extends AbstractController
{
    public function __invoke(
        Agora $agora,
        #[CurrentUser] Adherent $adherent,
        AgoraMembershipHandler $agoraMembershipHandler,
    ): Response {
        if (!$agora->published) {
            throw $this->createNotFoundException('Agora not found');
        }

        if ($agoraMembershipHandler->isMember($adherent, $agora)) {
            return $this->json([
                'status' => 'error',
                'message' => 'Vous êtes déjà membre de cette Agora.',
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($agora->isMembersFull()) {
            return $this->json([
                'status' => 'error',
                'message' => 'La limite de membres pour cette Agora a été atteinte.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $agoraMembershipHandler->add($adherent, $agora);

        return $this->json('OK', Response::HTTP_CREATED);
    }
}

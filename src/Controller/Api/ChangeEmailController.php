<?php

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Membership\AdherentChangeEmailHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v3/profile/email', name: 'app_api_user_profile_email')]
class ChangeEmailController extends AbstractController
{
    public function __construct(
        private readonly AdherentChangeEmailHandler $changeEmailHandler
    ) {
    }

    #[IsGranted('ROLE_OAUTH_SCOPE_WRITE:PROFILE')]
    #[Route(path: '/request', name: '_request', methods: ['POST'])]
    public function request(Request $request): JsonResponse
    {
        /** @var Adherent $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (!$newEmail = $data['email'] ?? null) {
            return new JsonResponse('Property "email" is required.', Response::HTTP_BAD_REQUEST);
        }

        if ($user->getEmailAddress() !== $newEmail) {
            $this->changeEmailHandler->handleRequest($user, $newEmail);

            return $this->json([
                'message' => 'Un mail vous a été envoyé pour confirmer votre changement d\'adresse email.',
            ]);
        }

        return $this->json([
            'message' => 'Aucun changement d\'adresse email.',
        ]);
    }
}

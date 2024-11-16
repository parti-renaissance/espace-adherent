<?php

namespace App\Controller\Api;

use App\AdherentProfile\AdherentProfile;
use App\Entity\Adherent;
use App\Membership\AdherentChangeEmailHandler;
use App\Repository\AdherentChangeEmailTokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/v3/profile/email', name: 'app_api_user_profile_email')]
class ChangeEmailController extends AbstractController
{
    public function __construct(
        private readonly AdherentChangeEmailHandler $changeEmailHandler,
    ) {
    }

    #[IsGranted('ROLE_OAUTH_SCOPE_WRITE:PROFILE')]
    #[Route(path: '/request', name: '_request', methods: ['POST'])]
    public function request(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
    ): JsonResponse {
        $json = $request->getContent();

        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $adherentProfile = AdherentProfile::createFromAdherent($adherent);

        $serializer->deserialize($json, AdherentProfile::class, 'json', [
            AbstractObjectNormalizer::OBJECT_TO_POPULATE => $adherentProfile,
            AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
            AbstractObjectNormalizer::GROUPS => [
                'profile_email_change',
            ],
        ]);

        $violations = $validator->validate($adherentProfile, null, ['api_email_change']);

        if (
            $adherent->getEmailAddress() !== $adherentProfile->getEmailAddress()
            && 0 === $violations->count()
        ) {
            $this->changeEmailHandler->handleRequest($adherent, $adherentProfile->getEmailAddress());

            return $this->json([
                'message' => 'Un mail vous a été envoyé pour confirmer votre changement d\'adresse email.',
            ]);
        }

        if (0 < $violations->count()) {
            $errors = $serializer->serialize($violations, 'jsonproblem');

            return JsonResponse::fromJsonString($errors, JsonResponse::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'message' => 'Aucun changement d\'adresse email.',
        ]);
    }

    #[IsGranted('ROLE_OAUTH_SCOPE_WRITE:PROFILE')]
    #[Route(path: '/send-validation', name: '_send_validation', methods: ['POST'])]
    public function sendValidationEmail(
        AdherentChangeEmailTokenRepository $changeEmailTokenRepository,
    ): JsonResponse {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $token = $changeEmailTokenRepository->findLastUnusedByAdherent($adherent);

        if (!$token) {
            return $this->json([
                'message' => 'Aucun changement d\'adresse email en attente de validation pour cet utilisateur.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->changeEmailHandler->sendValidationEmail($adherent, $token);

        return $this->json([
            'message' => 'Email de validation envoyé avec succès.',
        ]);
    }
}

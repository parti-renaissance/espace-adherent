<?php

namespace App\Controller\Api;

use App\AdherentProfile\AdherentProfile;
use App\Entity\Adherent;
use App\Membership\AdherentChangeEmailHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(path: '/v3/profile/email', name: 'app_api_user_profile_email')]
class ChangeEmailController extends AbstractController
{
    public function __construct(
        private readonly AdherentChangeEmailHandler $changeEmailHandler
    ) {
    }

    #[Route(path: '/request', name: '_request', methods: ['POST'])]
    #[Security("is_granted('ROLE_OAUTH_SCOPE_WRITE:PROFILE')")]
    public function request(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator
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

        $validationGroups = ['api_put_validation'];
        if ($adherent->isAdherent()) {
            $validationGroups[] = 'Default';
        }

        $violations = $validator->validate($adherentProfile, null, $validationGroups);

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
}

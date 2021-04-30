<?php

namespace App\Controller\Api;

use App\AdherentProfile\AdherentProfile;
use App\AdherentProfile\AdherentProfileConfiguration;
use App\AdherentProfile\AdherentProfileHandler;
use App\Entity\Adherent;
use App\Membership\MembershipRequestHandler;
use App\OAuth\TokenRevocationAuthority;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/v3/profile", name="app_api_user_profile")
 */
class ProfileController extends AbstractController
{
    private const READ_PROFILE_SERIALIZATION_GROUPS = [
        'profile_read',
    ];

    private const WRITE_PROFILE_SERIALIZATION_GROUPS = [
        'profile_write',
    ];

    private const WRITE_UNCERTIFIED_PROFILE_SERIALIZATION_GROUPS = [
        'uncertified_profile_write',
    ];

    /**
     * @Route("/me", name="_show", methods={"GET"})
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:PROFILE')")
     */
    public function show(SerializerInterface $serializer, UserInterface $user): JsonResponse
    {
        return JsonResponse::fromJsonString(
            $serializer->serialize($user, 'json', [
                AbstractObjectNormalizer::GROUPS => self::READ_PROFILE_SERIALIZATION_GROUPS,
            ])
        );
    }

    /**
     * @Route("/{uuid}", name="_update", methods={"PUT"})
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_WRITE:PROFILE') and user == adherent")
     */
    public function update(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        AdherentProfileHandler $handler,
        Adherent $adherent
    ): JsonResponse {
        $json = $request->getContent();

        $adherentProfile = AdherentProfile::createFromAdherent($adherent);

        $groups = self::WRITE_PROFILE_SERIALIZATION_GROUPS;
        if (!$adherent->isCertified()) {
            $groups = array_merge($groups, self::WRITE_UNCERTIFIED_PROFILE_SERIALIZATION_GROUPS);
        }

        $serializer->deserialize($json, AdherentProfile::class, 'json', [
            AbstractObjectNormalizer::OBJECT_TO_POPULATE => $adherentProfile,
            AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
            AbstractObjectNormalizer::GROUPS => $groups,
        ]);

        $validationGroups = ['api_put_validation'];
        if ($adherent->isAdherent()) {
            $validationGroups[] = 'Default';
        }

        $violations = $validator->validate($adherentProfile, null, $validationGroups);

        if (0 === $violations->count()) {
            $handler->update($adherent, $adherentProfile);

            return new JsonResponse('OK');
        }

        $errors = $serializer->serialize($violations, 'jsonproblem');

        return JsonResponse::fromJsonString($errors, JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/configuration", name="_configuration", methods={"GET"})
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_WRITE:PROFILE')")
     */
    public function configuration(AdherentProfileConfiguration $adherentProfileConfiguration): JsonResponse
    {
        return new JsonResponse($adherentProfileConfiguration->build());
    }

    /**
     * @Route("/unregister", name="_unregister", methods={"POST"})
     * @Security("is_granted('UNREGISTER')")
     */
    public function terminateMembershipAction(
        Request $request,
        MembershipRequestHandler $handler,
        TokenRevocationAuthority $tokenRevocationAuthority,
        TokenStorageInterface $tokenStorage,
        UserInterface $user
    ): Response {
        $handler->terminateMembership($user, null, false);
        $tokenRevocationAuthority->revokeUserTokens($user);

        return $this->json('OK');
    }
}

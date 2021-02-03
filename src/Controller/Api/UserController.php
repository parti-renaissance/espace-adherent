<?php

namespace App\Controller\Api;

use App\AdherentProfile\AdherentProfile;
use App\AdherentProfile\AdherentProfileHandler;
use App\Entity\Adherent;
use App\OAuth\Model\ClientApiUser;
use App\OAuth\Model\DeviceApiUser;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\Response as OAuthResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/me", name="app_api_user_show_me_for_oauth", methods={"GET"})
     */
    public function oauthShowMe(SerializerInterface $serializer, UserInterface $user)
    {
        if ($user instanceof ClientApiUser) {
            return OAuthServerException::accessDenied('API user does not have access to this route')
                ->generateHttpResponse(new OAuthResponse())
            ;
        }

        if ($user instanceof DeviceApiUser) {
            $user = $user->getDevice();
        }

        return new JsonResponse(
            $serializer->serialize($user, 'json', ['groups' => $this->getGrantedNormalizationGroups()]),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Route("/v3/users/{uuid}", name="app_api_user_update_profile", methods={"PUT"})
     *
     * @Security("user == adherent")
     */
    public function updateProfile(
        Request $request,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        AdherentProfileHandler $handler,
        Adherent $adherent
    ): Response {
        $json = $request->getContent();

        $adherentProfile = AdherentProfile::createFromAdherent($adherent);

        $groups = ['profile_update'];
        if (!$adherent->isCertified()) {
            $groups[] = 'uncertified_profile_update';
        }

        $serializer->deserialize($json, AdherentProfile::class, 'json', [
            AbstractNormalizer::OBJECT_TO_POPULATE => $adherentProfile,
            'groups' => $groups,
        ]);

        $violations = $validator->validate($adherentProfile);

        if (0 === $violations->count()) {
            $handler->update($adherent, $adherentProfile);

            return new JsonResponse(null, Response::HTTP_OK);
        }

        $errors = $serializer->serialize($violations, 'jsonproblem');

        return JsonResponse::fromJsonString($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Security("is_granted('ROLE_ADHERENT')")
     * @Route("/users/me", name="app_api_user_show_me", methods={"GET"})
     */
    public function showMe(SerializerInterface $serializer): JsonResponse
    {
        /* @var Adherent $user */
        $user = $this->getUser();
        $groups = ['user_profile', 'legacy'];

        if ($user->isReferent()) {
            $groups[] = 'referent';
        }

        return new JsonResponse(
            $serializer->serialize($this->getUser(), 'json', ['groups' => $groups]),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    private function getGrantedNormalizationGroups(): array
    {
        $groups = ['legacy'];

        if ($this->isGranted('ROLE_OAUTH_SCOPE_JEMARCHE_APP')) {
            $groups = ['jemarche_user_profile'];
        }

        $groups[] = 'user_profile';

        return $groups;
    }
}

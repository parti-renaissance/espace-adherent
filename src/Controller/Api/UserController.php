<?php

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\OAuth\Model\ClientApiUser;
use App\OAuth\Model\DeviceApiUser;
use League\OAuth2\Server\Exception\OAuthServerException;
use Nyholm\Psr7\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends Controller
{
    /**
     * @Route("/me", name="app_api_user_show_me_for_oauth", methods={"GET"})
     */
    public function oauthShowMe(SerializerInterface $serializer, UserInterface $user)
    {
        if ($user instanceof ClientApiUser) {
            return OAuthServerException::accessDenied('API user does not have access to this route')
                ->generateHttpResponse(new Response())
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

<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Adherent;
use AppBundle\OAuth\Model\ApiUser;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Zend\Diactoros\Response;

class UserController extends Controller
{
    /**
     * @Route("/me", name="app_api_user_show_me_for_oauth")
     * @Method("GET")
     */
    public function oauthShowMe(Serializer $serializer)
    {
        if ($this->getUser() instanceof ApiUser) {
            return OAuthServerException::accessDenied('API user does not have access to this route')
                ->generateHttpResponse(new Response())
            ;
        }

        return new JsonResponse(
            $serializer->serialize(
                $this->getUser(),
                'json',
                SerializationContext::create()->setGroups(['user_profile'])
            ),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @Security("is_granted('ROLE_ADHERENT')")
     * @Route("/users/me", name="app_api_user_show_me")
     * @Method("GET")
     */
    public function showMe(Serializer $serializer): JsonResponse
    {
        /* @var Adherent $user */
        $user = $this->getUser();
        $groups = ['user_profile'];

        if ($user->isReferent()) {
            $groups[] = 'referent';
        }

        return new JsonResponse(
            $serializer->serialize($user, 'json', SerializationContext::create()->setGroups($groups)),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }
}

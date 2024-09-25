<?php

namespace App\Controller\OAuth;

use App\Entity\Adherent;
use App\Entity\OAuth\Client;
use Firebase\JWT\JWT;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
#[Route(path: '/sso/{uuid}', name: 'app_front_oauth_sso', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
class SsoController extends AbstractController
{
    public function __invoke(Request $request, Client $client): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Adherent) {
            throw $this->createAccessDeniedException();
        }

        foreach ($client->getRequestedRoles() ?? [] as $role) {
            $this->denyAccessUnlessGranted($role, $user);
        }

        $clientRedirectUri = current($client->getRedirectUris());

        $queryParams = [
            'jwt' => $this->generateJwt($user, $client),
            'return_to' => $request->query->get('return_to'),
        ];

        return $this->redirect($clientRedirectUri.'?'.http_build_query($queryParams));
    }

    private function generateJwt(Adherent $user, Client $client): string
    {
        $payload = [
            'email' => $user->getEmailAddress(),
            'name' => $user->getFullName(),
            'locale' => 'fr',
        ];

        if ($user->getImagePath()) {
            $payload['profilePicture'] = $this->generateUrl('asset_url', ['path' => $user->getImagePath()]);
        }

        return JWT::encode($payload, $client->getSecret(), 'HS256');
    }
}

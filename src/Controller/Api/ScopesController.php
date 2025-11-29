<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Scope\GeneralScopeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ScopesController extends AbstractController
{
    #[Route(path: '/v3/profile/me/scopes', name: 'app_api_user_profile_scopes', methods: ['GET'])]
    public function __invoke(GeneralScopeGenerator $scopeGenerator): JsonResponse
    {
        /** @var Adherent $user */
        $user = $this->getUser();

        return $this->json(
            $user instanceof Adherent ? $scopeGenerator->generateScopes($user) : [],
            Response::HTTP_OK,
            [],
            ['groups' => ['scopes']]
        );
    }
}

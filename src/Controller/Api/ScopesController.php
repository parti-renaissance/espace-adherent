<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Scope\GeneralScopeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/v3/profile/me/scopes', name: 'app_api_user_profile_scopes', methods: ['GET'])]
class ScopesController extends AbstractController
{
    public function __invoke(#[CurrentUser] Adherent $adherent, GeneralScopeGenerator $scopeGenerator): Response
    {
        return $this->json($scopeGenerator->generateScopes($adherent), context: ['groups' => ['scopes']]);
    }
}

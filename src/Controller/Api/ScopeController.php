<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Scope\GeneralScopeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ScopeController extends AbstractController
{
    use ScopeTrait;

    public function __construct(GeneralScopeGenerator $generalScopeGenerator)
    {
        $this->generalScopeGenerator = $generalScopeGenerator;
    }

    #[Route(path: '/v3/profile/me/scope/{scopeCode}', name: 'app_api_user_profile_scope', methods: ['GET'])]
    public function __invoke(string $scopeCode): JsonResponse
    {
        /** @var Adherent $user */
        $user = $this->getUser();

        return $this->json(
            $this->getScope($scopeCode, $user),
            Response::HTTP_OK,
            [],
            ['groups' => ['scope']]
        );
    }
}

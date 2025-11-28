<?php

declare(strict_types=1);

namespace App\Controller\Api\Audience;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Geo\ManagedZoneProvider;
use App\Repository\Audience\AudienceRepository;
use App\Scope\AuthorizationChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class RetrieveAudiencesController extends AbstractController
{
    use AccessDelegatorTrait;

    public function __invoke(
        Request $request,
        ManagedZoneProvider $managedZoneProvider,
        AudienceRepository $repository,
        AuthorizationChecker $authorizationChecker,
    ): array {
        $scope = $authorizationChecker->getScope($request);
        $user = $this->getMainUser($request->getSession());

        return $repository->findByZones(
            $scope,
            $managedZoneProvider->getManagedZones($user, AdherentSpaceEnum::SCOPES[$scope])
        );
    }
}

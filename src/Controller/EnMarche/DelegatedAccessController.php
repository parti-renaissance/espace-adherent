<?php

namespace App\Controller\EnMarche;

use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\DelegatedAccessEnum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class DelegatedAccessController extends AbstractController
{
    #[Route(path: '/espace-partage/{uuid}', name: 'app_access_delegation_set', methods: ['GET'])]
    public function delegatedSpace(Request $request, DelegatedAccess $delegatedAccess)
    {
        if (0 === \count($delegatedAccess->getAccesses())) {
            throw new \LogicException(\sprintf('No access available for delegated access %d', $delegatedAccess->getId()));
        }

        $request->getSession()->set(DelegatedAccess::ATTRIBUTE_KEY, $delegatedAccess->getUuid()->toString());

        $routes = DelegatedAccessEnum::getDelegatedAccessRoutes($delegatedAccess->getType());

        return $this->redirectToRoute($routes[$delegatedAccess->getAccesses()[0]]);
    }

    #[Route(path: '/espace-standard/{type}', name: 'app_access_delegation_unset', methods: ['GET'])]
    public function standardSpace(Request $request, string $type)
    {
        $request->getSession()->remove(DelegatedAccess::ATTRIBUTE_KEY);

        return $this->redirectToRoute(DelegatedAccessEnum::getStandardRoute($type), $request->query->all());
    }
}

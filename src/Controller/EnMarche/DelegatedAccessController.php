<?php

namespace App\Controller\EnMarche;

use App\Entity\MyTeam\DelegatedAccess;
use App\Entity\MyTeam\DelegatedAccessEnum;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class DelegatedAccessController extends Controller
{
    /**
     * @Route("/espace-partage/{uuid}", name="app_access_delegation_set", methods={"GET"})
     */
    public function delegatedSpace(DelegatedAccess $delegatedAccess, SessionInterface $session)
    {
        if (0 === \count($delegatedAccess->getAccesses())) {
            throw new \LogicException(sprintf('No access available for delegated access %d', $delegatedAccess->getId()));
        }

        $session->set(DelegatedAccess::ATTRIBUTE_KEY, $delegatedAccess->getUuid()->toString());

        $routes = DelegatedAccessEnum::getDelegatedAccessRoutes($delegatedAccess->getType());

        return $this->redirectToRoute($routes[$delegatedAccess->getAccesses()[0]]);
    }

    /**
     * @Route("/espace-standard/{type}", name="app_access_delegation_unset", methods={"GET"})
     */
    public function standardSpace(Request $request, string $type, SessionInterface $session)
    {
        $session->remove(DelegatedAccess::ATTRIBUTE_KEY);

        return $this->redirectToRoute(DelegatedAccessEnum::getStandardRoute($type), $request->query->all());
    }
}

<?php

namespace App\Controller\Api\AdherentList;

use App\Repository\Projection\ManagedUserRepository;
use App\Scope\ScopeGeneratorResolver;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v3/adherents/count", name="app_adherents_count_get", methods={"GET"})
 *
 * @Security("is_granted('IS_FEATURE_GRANTED', 'dashboard')")
 */
class CountAdherentController extends AbstractController
{
    public function __invoke(ManagedUserRepository $managedUserRepository, ScopeGeneratorResolver $resolver): Response
    {
        $scope = $resolver->generate();

        return $this->json(['adherent_count' => $managedUserRepository->countManagedUsers($scope->getZones())], Response::HTTP_OK);
    }
}

<?php

namespace App\Controller\Api\MyTeam;

use App\Entity\MyTeam\MyTeam;
use App\Repository\MyTeam\MyTeamRepository;
use App\Scope\ScopeGeneratorResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class InitializeMyTeamController extends AbstractController
{
    public function __invoke(
        Request $request,
        ScopeGeneratorResolver $scopeGeneratorResolver,
        MyTeamRepository $myTeamRepository,
        EntityManagerInterface $entityManager
    ): ?MyTeam {
        if (!$scopeCode = $request->query->get('scope')) {
            throw new BadRequestHttpException('Pas de scope.');
        }

        $scope = $scopeGeneratorResolver->generate();
        if (!$scope) {
            throw new BadRequestHttpException('L\'utilisateur n\'a pas de scope demandé.');
        }

        $user = $this->getUser();
        $myTeam = $myTeamRepository->findOneByAdherentAndScope($user, $scopeCode);
        if ($myTeam) {
            return $myTeam;
        }

        $myTeam = new MyTeam($user, $scopeCode);
        $entityManager->persist($myTeam);
        $entityManager->flush();

        return $myTeam;
    }
}

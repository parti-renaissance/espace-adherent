<?php

declare(strict_types=1);

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
        EntityManagerInterface $entityManager,
    ): ?MyTeam {
        if (!$scopeCode = $request->query->get('scope')) {
            throw new BadRequestHttpException('Aucun scope renseigné.');
        }

        if (null === $scopeGeneratorResolver->resolve()) {
            throw new BadRequestHttpException('Vous n\'avez pas accès au scope demandé.');
        }

        $user = $this->getUser();
        $myTeam = $myTeamRepository->findOneByAdherentAndScope($user, $scopeCode);
        if ($myTeam) {
            return $myTeam;
        }

        return new MyTeam($user, $scopeCode);
    }
}

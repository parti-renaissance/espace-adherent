<?php

declare(strict_types=1);

namespace App\Controller\EnMarche\Jecoute;

use App\Entity\Adherent;
use App\Jecoute\JecouteSpaceEnum;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_CANDIDATE') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_JECOUTE'))"))]
#[Route(path: '/espace-candidat/questionnaires', name: 'app_jecoute_candidate_')]
class JecouteCandidateController extends AbstractJecouteController
{
    protected function getSpaceName(): string
    {
        return JecouteSpaceEnum::CANDIDATE_SPACE;
    }

    protected function getZones(Adherent $adherent): array
    {
        return [$adherent->getCandidateManagedArea()->getZone()];
    }
}

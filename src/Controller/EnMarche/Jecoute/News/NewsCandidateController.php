<?php

namespace App\Controller\EnMarche\Jecoute\News;

use App\Entity\Adherent;
use App\Jecoute\JecouteSpaceEnum;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_JECOUTE_NEWS') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_JECOUTE_NEWS'))"))]
#[Route(path: '/espace-candidat/actualites', name: 'app_jecoute_news_candidate_')]
class NewsCandidateController extends AbstractNewsController
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

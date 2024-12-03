<?php

namespace App\Controller\EnMarche\Jecoute\News;

use App\Entity\Adherent;
use App\Jecoute\JecouteSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/espace-candidat/actualites', name: 'app_jecoute_news_candidate_')]
#[Security("is_granted('ROLE_JECOUTE_NEWS') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_JECOUTE_NEWS'))")]
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

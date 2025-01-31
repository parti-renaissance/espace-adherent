<?php

namespace App\Controller\EnMarche\Poll;

use App\AdherentSpace\AdherentSpaceEnum;
use App\Entity\Adherent;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression("is_granted('ROLE_CANDIDATE_REGIONAL_HEADED') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_POLLS'))"))]
#[Route(path: '/espace-candidat/question-du-jour', name: 'app_candidate_polls_')]
class PollCandidateController extends AbstractPollController
{
    protected function getSpaceName(): string
    {
        return AdherentSpaceEnum::CANDIDATE;
    }

    protected function getZones(Adherent $adherent): array
    {
        return [$adherent->getCandidateManagedArea()->getZone()];
    }
}

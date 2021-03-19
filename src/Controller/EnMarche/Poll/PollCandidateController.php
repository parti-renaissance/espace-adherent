<?php

namespace App\Controller\EnMarche\Poll;

use App\Entity\Adherent;
use App\Poll\PollSpaceEnum;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-candidat/question-du-jour", name="app_candidate_polls_")
 *
 * @Security("is_granted('ROLE_CANDIDATE_REGIONAL_HEADED') or (is_granted('ROLE_DELEGATED_CANDIDATE') and is_granted('HAS_DELEGATED_ACCESS_POLLS'))")
 */
class PollCandidateController extends AbstractPollController
{
    protected function getSpaceName(): string
    {
        return PollSpaceEnum::CANDIDATE_SPACE;
    }

    protected function getZones(Adherent $adherent): array
    {
        return [$adherent->getCandidateManagedArea()->getZone()];
    }
}

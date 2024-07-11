<?php

namespace App\Controller\EnMarche\MyTeam;

use App\Entity\Adherent;
use App\Repository\CommitteeRepository;
use App\Repository\ReferentTagRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_CANDIDATE')]
#[Route(path: '/espace-candidat/mon-equipe', name: 'app_candidate_my_team_', methods: ['GET'])]
class CandidateMyTeamController extends AbstractMyTeamController
{
    private $referentTagRepository;

    public function __construct(ReferentTagRepository $referentTagRepository)
    {
        $this->referentTagRepository = $referentTagRepository;
    }

    protected function getSpaceType(): string
    {
        return 'candidate';
    }

    protected function getCommittees(Adherent $adherent, string $term, CommitteeRepository $committeeRepository): array
    {
        $referentTags = $this->referentTagRepository->findByZones([$adherent->getCandidateManagedArea()->getZone()]);

        return $committeeRepository->findByPartialNameForCandidate($referentTags, $term);
    }

    protected function getManagedTags(Adherent $adherent): array
    {
        return $this->referentTagRepository->findByZones($this->getZones($adherent));
    }

    protected function getZones(Adherent $adherent): array
    {
        return [$adherent->getCandidateManagedArea()->getZone()];
    }
}

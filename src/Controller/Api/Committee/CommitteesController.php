<?php

declare(strict_types=1);

namespace App\Controller\Api\Committee;

use App\Committee\Election\CandidacyManager;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Repository\CommitteeMembershipRepository;
use App\Security\Voter\Committee\CommitteeElectionVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommitteesController extends AbstractController
{
    #[IsGranted('MEMBER_OF_COMMITTEE', subject: 'committee')]
    #[Route(path: '/committees/{uuid}/candidacies', name: 'app_api_committee_candidacies_get', methods: ['GET'])]
    public function getCommitteeCandidaciesAction(
        Committee $committee,
        CommitteeMembershipRepository $repository,
    ): Response {
        $memberships = $repository->getCandidacyMemberships($committee->getCommitteeElection());

        return $this->json([
            'metadata' => [
                'total' => \count($memberships),
                'males' => \count(array_filter($memberships, static function (CommitteeMembership $membership) {
                    return !$membership->getCommitteeCandidacy($membership->getCommittee()->getCommitteeElection())->isFemale();
                })),
                'females' => \count(array_filter($memberships, static function (CommitteeMembership $membership) {
                    return $membership->getCommitteeCandidacy($membership->getCommittee()->getCommitteeElection())->isFemale();
                })),
            ],
            'candidacies' => array_map(function (CommitteeMembership $membership) {
                $candidacy = $membership->getCommitteeCandidacy($membership->getCommittee()->getCommitteeElection());

                return [
                    'photo' => $candidacy->getImageName() ? $this->generateUrl('asset_url', ['path' => $candidacy->getImagePath()]) : null,
                    'gender' => $candidacy->getGender(),
                    'first_name' => $membership->getAdherent()->getFirstName(),
                    'last_name' => $membership->getAdherent()->getLastName(),
                    'created_at' => $candidacy->getCreatedAt(),
                ];
            }, $memberships),
        ]);
    }

    #[IsGranted('MEMBER_OF_COMMITTEE', subject: 'committee')]
    #[Route(path: '/committee/{slug}/candidacy/available-memberships', name: 'api_committee_candidacy_available_memberships_get', methods: ['GET'])]
    public function getAvailableMembershipsAction(
        Committee $committee,
        Request $request,
        CandidacyManager $candidacyManager,
        CommitteeMembershipRepository $repository,
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        if (!($election = $committee->getCommitteeElection()) || !$election->isCandidacyPeriodActive()) {
            throw $this->createAccessDeniedException('No election is started');
        }

        if (!$candidacy = $candidacyManager->getCandidacy($adherent, $committee)) {
            throw $this->createAccessDeniedException('You do not have a candidacy');
        }

        $this->denyAccessUnlessGranted(CommitteeElectionVoter::PERMISSION_ABLE_TO_CANDIDATE, $committee);

        if (!$query = trim($request->query->get('query', ''))) {
            return $this->json(
                ['message' => 'Veuillez utiliser la recherche pour retrouver votre binôme'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        return $this->json(
            $repository->findAvailableMemberships($candidacy, $query),
            JsonResponse::HTTP_OK,
            [],
            ['groups' => ['api_candidacy_read']]
        );
    }
}

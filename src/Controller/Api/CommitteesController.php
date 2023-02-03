<?php

namespace App\Controller\Api;

use App\Api\CommitteeProvider;
use App\Committee\Election\CandidacyManager;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Repository\CommitteeMembershipRepository;
use App\Security\Voter\Committee\CommitteeElectionVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommitteesController extends AbstractController
{
    /**
     * @Route("/committees", name="api_committees", methods={"GET"})
     */
    public function getApprovedCommitteesAction(CommitteeProvider $provider): Response
    {
        return new JsonResponse($provider->getApprovedCommittees());
    }

    /**
     * @Route("/committees/{uuid}/candidacies", name="app_api_committee_candidacies_get", methods={"GET"})
     *
     * @Security("is_granted('MEMBER_OF_COMMITTEE', committee) or is_granted('ROLE_REFERENT') or is_granted('ROLE_DELEGATED_REFERENT')")
     */
    public function getCommitteeCandidaciesAction(
        Committee $committee,
        CommitteeMembershipRepository $repository
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

    /**
     * @Route("/committee/{slug}/candidacy/available-memberships", name="api_committee_candidacy_available_memberships_get", methods={"GET"})
     *
     * @IsGranted("MEMBER_OF_COMMITTEE", subject="committee")
     */
    public function getAvailableMembershipsAction(
        Committee $committee,
        Request $request,
        CandidacyManager $candidacyManager,
        CommitteeMembershipRepository $repository
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

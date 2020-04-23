<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\History\CommitteeMembershipHistoryHandler;
use AppBundle\Repository\AdherentRepository;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\CommitteeRepository;
use AppBundle\Statistics\StatisticsParametersFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class CommitteesController extends Controller
{
    /**
     * @Route("/committees", name="api_committees", methods={"GET"})
     */
    public function getApprovedCommitteesAction(): Response
    {
        return new JsonResponse($this->get('app.api.committee_provider')->getApprovedCommittees());
    }

    /**
     * @Route("/statistics/committees/count-for-referent-area", name="app_committees_count_for_referent_area", methods={"GET"})
     * @Entity("referent", expr="repository.findReferent(referent)", converter="querystring")
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:STATS')")
     */
    public function getCommitteeCountersAction(
        Adherent $referent,
        AdherentRepository $adherentRepository,
        CommitteeRepository $committeeRepository
    ): Response {
        return new JsonResponse([
            'committees' => $committeeRepository->countApprovedForReferent($referent),
            'members' => $adherentRepository->countMembersByGenderForReferent($referent),
            'supervisors' => $adherentRepository->countSupervisorsByGenderForReferent($referent),
        ]);
    }

    /**
     * @Route("/statistics/committees/members/count-by-month", name="app_committee_members_count_by_month_for_referent_area", methods={"GET"})
     * @Entity("referent", expr="repository.findReferent(referent)", converter="querystring")
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:STATS')")
     */
    public function getMembersCommitteeCountAction(
        Request $request,
        Adherent $referent,
        CommitteeMembershipHistoryHandler $committeeMembershipHistoryHandler
    ): Response {
        $filter = StatisticsParametersFilter::createFromRequest($request, $this->getDoctrine()->getRepository(Committee::class));

        return new JsonResponse(['committee_members' => $committeeMembershipHistoryHandler->queryCountByMonth($referent, 6, $filter)]);
    }

    /**
     * @Route("/statistics/committees/top-5-in-referent-area", name="app_most_active_committees", methods={"GET"})
     * @Entity("referent", expr="repository.findReferent(referent)", converter="querystring")
     *
     * @Security("is_granted('ROLE_OAUTH_SCOPE_READ:STATS')")
     */
    public function getTopCommitteesInReferentManagedAreaAction(
        Adherent $referent,
        CommitteeRepository $committeeRepository
    ): Response {
        return new JsonResponse([
            'most_active' => $committeeRepository->retrieveMostActiveCommitteesInReferentManagedArea($referent),
            'least_active' => $committeeRepository->retrieveLeastActiveCommitteesInReferentManagedArea($referent),
        ]);
    }

    /**
     * @Route("/committees/{uuid}/candidacies", name="app_api_committee_candidacies_get", methods={"GET"})
     *
     * @Security("is_granted('MEMBER_OF_COMMITTEE', committee)")
     */
    public function getCommitteeCandidaciesAction(
        Committee $committee,
        CommitteeMembershipRepository $repository
    ): Response {
        $memberships = $repository->getCandidacyMemberships($committee);

        return $this->json([
            'metadata' => [
                'total' => \count($memberships),
                'males' => \count(array_filter($memberships, static function (CommitteeMembership $membership) {
                    return $membership->getCommitteeCandidacy()->isMale();
                })),
                'females' => \count(array_filter($memberships, static function (CommitteeMembership $membership) {
                    return $membership->getCommitteeCandidacy()->isFemale();
                })),
            ],
            'candidacies' => array_map(static function (CommitteeMembership $membership) {
                return [
                    'gender' => $membership->getCommitteeCandidacy()->getGender(),
                    'first_name' => $membership->getAdherent()->getFirstName(),
                    'last_name' => $membership->getAdherent()->getLastName(),
                    'created_at' => $membership->getCommitteeCandidacy()->getCreatedAt(),
                ];
            }, $memberships),
        ]);
    }

    /**
     * ApiPlatform action
     *
     * @see Committee
     */
    public function myCommitteesAction(
        CommitteeMembershipRepository $committeeMembershipRepository,
        UserInterface $user
    ): array {
        return array_map(function (CommitteeMembership $committeeMembership) {
            return $committeeMembership->getCommittee();
        }, $committeeMembershipRepository->findMembershipsForActiveCommittees($user)->toArray());
    }
}

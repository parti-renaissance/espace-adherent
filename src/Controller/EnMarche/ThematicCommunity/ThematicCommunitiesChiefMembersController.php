<?php

namespace App\Controller\EnMarche\ThematicCommunity;

use App\Entity\Adherent;
use App\Form\ThematicCommunityMembershipFilterType;
use App\Repository\ThematicCommunity\ThematicCommunityMembershipRepository;
use App\ThematicCommunity\ThematicCommunityMembershipFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/communautes-thematiques', name: 'app_thematic_community_')]
#[IsGranted('ROLE_THEMATIC_COMMUNITY_CHIEF')]
class ThematicCommunitiesChiefMembersController extends AbstractController
{
    #[Route(path: '/membres', name: 'members_list')]
    public function members(Request $request, ThematicCommunityMembershipRepository $membershipRepository)
    {
        /** @var Adherent $user */
        $user = $this->getUser();
        $handledCommunities = $user->getHandledThematicCommunities()->toArray();

        $form = $this->createForm(ThematicCommunityMembershipFilterType::class, $filter = new ThematicCommunityMembershipFilter($handledCommunities), [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
            'handled_communities' => $handledCommunities,
        ])->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = new ThematicCommunityMembershipFilter($handledCommunities);
        }

        $members = $membershipRepository->searchByFilter($filter, $request->query->getInt('page', 1));

        return $this->render('thematic_community/members.html.twig', [
            'members' => $members,
            'total_count' => $membershipRepository->countMembershipsInCommunities($handledCommunities),
            'form' => $form->createView(),
            'filter' => $filter,
        ]);
    }
}

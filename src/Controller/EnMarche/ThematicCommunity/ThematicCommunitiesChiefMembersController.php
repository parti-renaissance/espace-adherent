<?php

namespace App\Controller\EnMarche\ThematicCommunity;

use App\Form\ThematicCommunityMembershipFilterType;
use App\Repository\ThematicCommunity\ThematicCommunityMembershipRepository;
use App\ThematicCommunity\ThematicCommunityMembershipFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/communautes-thematiques", name="app_thematic_community_")
 * @Security("is_granted('ROLE_THEMATIC_COMMUNITY_CHIEF')")
 */
class ThematicCommunitiesChiefMembersController extends AbstractController
{
    /**
     * @Route("/membres", name="members_list")
     */
    public function members(
        Request $request,
        UserInterface $user,
        ThematicCommunityMembershipRepository $membershipRepository
    ) {
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

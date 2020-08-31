<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Form\TerritorialCouncil\MemberFilterType;
use App\Repository\TerritorialCouncil\TerritorialCouncilMembershipRepository;
use App\Subscription\SubscriptionTypeEnum;
use App\TerritorialCouncil\Filter\MembersListFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent/conseil-territorial", name="app_referent_territorial_council_")
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentTerritorialCouncilController extends AbstractController
{
    use AccessDelegatorTrait;

    /**
     * @Route("/membres", name="members_list", methods={"GET"})
     */
    public function membersListAction(
        Request $request,
        TerritorialCouncilMembershipRepository $membershipRepository
    ): Response {
        $referentTags = $this->getMainUser($request->getSession())->getManagedArea()->getTags()->toArray();
        $filter = new MembersListFilter($referentTags, SubscriptionTypeEnum::REFERENT_EMAIL);
        $form = $this->createForm(MemberFilterType::class, $filter, [
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = new MembersListFilter($referentTags, SubscriptionTypeEnum::REFERENT_EMAIL);
        }

        $memberships = $membershipRepository->searchByFilter($filter, $request->query->getInt('page', 1));

        return $this->render('referent/territorial_council/members.html.twig', [
            'memberships' => $memberships,
            'filter' => $filter,
            'form' => $form->createView(),
            'total_count' => $membershipRepository->countForReferentTags($referentTags),
        ]);
    }
}

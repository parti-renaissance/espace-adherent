<?php

namespace App\Controller\EnMarche\TerritorialCouncil;

use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\ReferentTag;
use App\Exporter\TerritorialCouncilMembersExporter;
use App\Form\TerritorialCouncil\MemberFilterType;
use App\Repository\TerritorialCouncil\TerritorialCouncilMembershipRepository;
use App\Subscription\SubscriptionTypeEnum;
use App\TerritorialCouncil\Filter\MembersListFilter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("ROLE_REFERENT")
 */
#[Route(path: '/espace-referent/instances', name: 'app_territorial_council_referent_')]
class ReferentTerritorialCouncilController extends AbstractController
{
    use AccessDelegatorTrait;

    #[Route(path: '/membres.{_format}', name: 'members_list', methods: ['GET'], defaults: ['_format' => 'html'], requirements: ['_format' => 'html|csv|xls'])]
    public function membersListAction(
        Request $request,
        string $_format,
        TerritorialCouncilMembershipRepository $membershipRepository,
        TerritorialCouncilMembersExporter $exporter
    ): Response {
        $referentTags = $this->getFilteredReferentTags($this->getMainUser($request->getSession())->getManagedArea()->getTags()->toArray());
        $filter = new MembersListFilter($referentTags, SubscriptionTypeEnum::REFERENT_EMAIL);
        $form = $this->createForm(MemberFilterType::class, $filter, [
            'referent_tags' => $referentTags,
            'method' => Request::METHOD_GET,
            'csrf_protection' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = new MembersListFilter($referentTags, SubscriptionTypeEnum::REFERENT_EMAIL);
        }

        if ('html' !== $_format) {
            return $exporter->getResponse($_format, $filter);
        }

        $memberships = $membershipRepository->searchByFilter($filter, $request->query->getInt('page', 1));

        return $this->render('referent/territorial_council/members.html.twig', [
            'memberships' => $memberships,
            'filter' => $filter,
            'form' => $form->createView(),
            'total_count' => $membershipRepository->countForReferentTags($referentTags),
        ]);
    }

    /**
     * @param ReferentTag[] $referentTags
     *
     * @return ReferentTag[]
     */
    private function getFilteredReferentTags(array $referentTags): array
    {
        return array_filter($referentTags, function (ReferentTag $referentTag) {
            return !$referentTag->isCountryTag();
        });
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\TerritorialCouncil\Exception\PoliticalCommitteeMembershipException;
use App\TerritorialCouncil\PoliticalCommitteeManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/territorialcouncil")
 */
class AdminTerritorialCouncilController extends Controller
{
    private $politicalCommitteeManager;
    private $translator;

    public function __construct(PoliticalCommitteeManager $politicalCommitteeManager, TranslatorInterface $translator)
    {
        $this->politicalCommitteeManager = $politicalCommitteeManager;
        $this->translator = $translator;
    }

    /**
     * @Route("/{territorialCouncil}/members/{adherent}/{action}-membership", name="app_admin_territorial_council_change_political_committee_membership", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_TERRITORIAL_COUNCIL')")
     */
    public function changePoliticalCommitteeMembershipAction(
        Request $request,
        TerritorialCouncil $territorialCouncil,
        Adherent $adherent,
        string $action
    ): Response {
        if (!\in_array($action, PoliticalCommitteeManager::ACTIONS)) {
            throw new BadRequestHttpException(\sprintf('Action "%s" is not authorized.', $action));
        }

        if (!$this->isCsrfTokenValid(\sprintf('territorial_council.change_political_committee_membership.%s', $adherent->getId()), $request->query->get('token'))) {
            throw new BadRequestHttpException('Invalid Csrf token provided.');
        }

        try {
            if (PoliticalCommitteeManager::CREATE_ACTION === $action) {
                $this->politicalCommitteeManager->createMayorOrLeaderMembership($territorialCouncil, $adherent);
            } elseif (PoliticalCommitteeManager::REMOVE_ACTION === $action) {
                $this->politicalCommitteeManager->removeMayorOrLeaderMembership($territorialCouncil, $adherent);
            }
        } catch (PoliticalCommitteeMembershipException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('admin_app_territorialcouncil_territorialcouncil_territorialcouncil_territorialcouncilmembership_list', [
            'id' => $territorialCouncil->getId(),
            'filter' => ['territorialCouncil' => ['value' => $territorialCouncil->getId()]],
        ]);
    }
}

<?php

namespace App\Controller\EnMarche\TerritorialCouncil\Designation;

use App\Entity\Adherent;
use App\Entity\ReferentTag;
use App\Entity\TerritorialCouncil\Election;
use App\Form\TerritorialCouncil\EditDesignationType;
use App\Repository\TerritorialCouncil\ElectionRepository;
use App\TerritorialCouncil\Designation\UpdateDesignationHandler;
use App\TerritorialCouncil\Designation\UpdateDesignationRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-referent/instances", name="app_territorial_council_referent_designations", methods={"GET"})
 *
 * @IsGranted("ROLE_REFERENT")
 */
class ReferentDesignationController extends AbstractController
{
    private $electionRepository;

    public function __construct(ElectionRepository $electionRepository)
    {
        $this->electionRepository = $electionRepository;
    }

    /**
     * @Route("/designations", name="_list", methods={"GET"})
     */
    public function listAction(): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        return $this->render('territorial_council_designation/list.html.twig', [
            'elections' => $this->electionRepository->findAllForReferentTags(
                $this->getFilteredReferentTags($adherent->getManagedArea()->getTags()->toArray())
            ),
        ]);
    }

    /**
     * @Route("/{uuid}/convocation", name="_election_send_convocation", methods={"GET", "POST"}, requirements={"uuid": "%pattern_uuid%"})
     *
     * @Security("is_granted('CAN_MANAGE_TERRITORIAL_COUNCIL', election.getTerritorialCouncil())")
     */
    public function editDesignationAction(
        Request $request,
        Election $election,
        UpdateDesignationHandler $updateDesignationHandler
    ): Response {
        if (!$election->getTerritorialCouncil()->getMemberships()->getPresident()) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier les dates du Conseil territorial sans président.');

            return $this->redirectToRoute('app_territorial_council_referent_designations_list');
        }

        $designation = $election->getDesignation();

        if ($designation->getVoteStartDate()) {
            $this->addFlash('error', 'La désignation ne peut plus être modifiée');

            return $this->redirectToRoute('app_territorial_council_referent_designations_list');
        }

        $form = $this
            ->createForm(EditDesignationType::class, $updateRequest = new UpdateDesignationRequest())
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $updateDesignationHandler($updateRequest, $election);

            $this->addFlash('info', 'La convocation a bien été envoyée');

            return $this->redirectToRoute('app_territorial_council_referent_designations_list');
        }

        return $this->render('territorial_council_designation/convocation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function getFilteredReferentTags(array $referentTags): array
    {
        return array_filter($referentTags, function (ReferentTag $referentTag) {
            return !$referentTag->isCountryTag();
        });
    }
}

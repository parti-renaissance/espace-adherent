<?php

namespace App\Controller\EnMarche\TerritorialCouncil\Designation;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\Election;
use App\Form\TerritorialCouncil\EditDesignationType;
use App\Repository\TerritorialCouncil\ElectionRepository;
use App\TerritorialCouncil\Designation\UpdateDesignationHandler;
use App\TerritorialCouncil\Designation\UpdateDesignationRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route(path="/espace-referent/instances", name="app_territorial_council_referent", methods={"GET"})
 *
 * @Security("is_granted('ROLE_REFERENT')")
 */
class ReferentDesignationController extends AbstractController
{
    private $electionRepository;

    public function __construct(ElectionRepository $electionRepository)
    {
        $this->electionRepository = $electionRepository;
    }

    /**
     * @Route("/designations", name="_designations_list", methods={"GET"})
     *
     * @param Adherent $adherent
     */
    public function listAction(UserInterface $adherent): Response
    {
        return $this->render('territorial_council_designation/list.html.twig', [
            'elections' => $this->electionRepository->findAllForReferentTags($adherent->getManagedArea()->getTags()->toArray()),
        ]);
    }

    /**
     * @Route("/{uuid}/convocation", name="_election_send_convocation", methods={"GET", "POST"})
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
}

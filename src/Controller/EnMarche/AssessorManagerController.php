<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Assessor\AssessorManager;
use AppBundle\Assessor\Filter\AssessorRequestFilters;
use AppBundle\Assessor\Filter\VotePlaceFilters;
use AppBundle\Entity\ActionEnum;
use AppBundle\Entity\AssessorRequest;
use AppBundle\Entity\VotePlace;
use AppBundle\Exception\AssessorException;
use AppBundle\Form\ConfirmActionType;
use AppBundle\Repository\VotePlaceRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-responsable-assesseur")
 * @Security("is_granted('ROLE_ASSESSOR_MANAGER')")
 */
class AssessorManagerController extends Controller
{
    /**
     * @Route(name="app_assessor_manager_requests", methods={"GET"})
     */
    public function assessorRequestsAction(Request $request, AssessorManager $manager): Response
    {
        try {
            $filters = AssessorRequestFilters::fromRequest($request);
        } catch (AssessorException $e) {
            throw new BadRequestHttpException('Unexpected assessor request in the query string.', $e);
        }

        return $this->render('assessor_manager/requests.html.twig', [
            'requests' => $manager->getAssessorRequests($this->getUser(), $filters),
            'total_count' => $manager->countAssessorRequests($this->getUser(), $filters),
            'filters' => $filters,
        ]);
    }

    /**
     * @Route(
     *     "/plus",
     *     name="app_assessor_manager_requests_list",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"}
     * )
     */
    public function assessorRequestsMoreAction(Request $request, AssessorManager $manager): Response
    {
        try {
            $filters = AssessorRequestFilters::fromRequest($request);
        } catch (AssessorException $e) {
            throw new BadRequestHttpException('Unexpected assessor request in the query string.', $e);
        }

        if (!$requests = $manager->getAssessorRequests($this->getUser(), $filters)) {
            return new Response();
        }

        return $this->render('assessor_manager/_requests_list.html.twig', [
            'requests' => $requests,
        ]);
    }

    /**
     * @Route(
     *     "/demande/{uuid}",
     *     requirements={"uuid": "%pattern_uuid%"},
     *     name="app_assessor_manager_request",
     *     methods={"GET"}
     * )
     * @Security("is_granted('MANAGE', assessorRequest)")
     */
    public function assessorRequestAction(
        AssessorRequest $assessorRequest,
        VotePlaceRepository $votePlaceRepository
    ): Response {
        return $this->render('assessor_manager/request.html.twig', [
            'request' => $assessorRequest,
            'matchingVotePlaces' => $votePlaceRepository->findMatchingVotePlaces($assessorRequest),
        ]);
    }

    /**
     * @Route(
     *     "/demande/{uuid}/associer/{votePlaceId}",
     *     requirements={"uuid": "%pattern_uuid%", "votePlaceId": "\d+"},
     *     name="app_assessor_manager_request_associate",
     *     methods={"GET", "POST"}
     * )
     * @ParamConverter("assessorRequest", class="AppBundle\Entity\AssessorRequest", options={"mapping": {"uuid": "uuid"}})
     * @ParamConverter("votePlace", class="AppBundle\Entity\VotePlace", options={"id": "votePlaceId"})
     * @Security("is_granted('MANAGE', assessorRequest)")
     */
    public function assessorRequestAssociateAction(
        Request $request,
        VotePlaceRepository $votePlaceRepository,
        AssessorManager $manager,
        AssessorRequest $assessorRequest,
        VotePlace $votePlace
    ): Response {
        if (!$assessorRequest->isEnabled() || !\in_array($votePlace, $votePlaceRepository->findMatchingVotePlaces($assessorRequest))) {
            throw $this->createNotFoundException('No vote place for this request.');
        }

        $form = $this->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $manager->processAssessorRequest($assessorRequest, $votePlace);
                $this->addFlash('info', 'assessor.associate.success');
            }

            return $this->redirectToRoute('app_assessor_manager_request', ['uuid' => $assessorRequest->getUuid()]);
        }

        return $this->render('assessor_manager/associate.html.twig', [
            'form' => $form->createView(),
            'request' => $assessorRequest,
            'votePlace' => $votePlace,
        ]);
    }

    /**
     * @Route(
     *     "/demande/{uuid}/desassocier",
     *     requirements={"uuid": "%pattern_uuid%"},
     *     name="app_assessor_manager_request_deassociate",
     *     methods={"GET", "POST"}
     * )
     * @Security("is_granted('MANAGE', assessorRequest)")
     */
    public function assessorRequestDessociateAction(
        Request $request,
        AssessorRequest $assessorRequest,
        AssessorManager $manager
    ): Response {
        if (!$assessorRequest->getVotePlace()) {
            throw $this->createNotFoundException('This assessor request has no vote place.');
        }

        $form = $this->createForm(ConfirmActionType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $manager->unprocessAssessorRequest($assessorRequest);
                $this->addFlash('info', 'assessor.deassociate.success');
            }

            return $this->redirectToRoute('app_assessor_manager_request', ['uuid' => $assessorRequest->getUuid()]);
        }

        return $this->render('assessor_manager/deassociate.html.twig', [
            'form' => $form->createView(),
            'request' => $assessorRequest,
            'votePlace' => $assessorRequest->getVotePlace(),
        ]);
    }

    /**
     * @Route(
     *     "/transform/{uuid}/{action}",
     *     requirements={"uuid": "%pattern_uuid%", "action": AppBundle\Entity\ActionEnum::ACTIONS_URI_REGEX },
     *     name="app_assessor_manager_request_transform",
     *     methods={"GET"}
     * )
     * @Security("is_granted('MANAGE', assessorRequest)")
     */
    public function assessorRequestTransformAction(
        AssessorRequest $assessorRequest,
        string $action,
        AssessorManager $manager
    ): Response {
        if (ActionEnum::ACTION_DISABLE === $action) {
            $manager->disableAssessorRequest($assessorRequest);
            $this->addFlash('info', 'assessor.disabled.success');
        } else {
            $manager->enableAssessorRequest($assessorRequest);
            $this->addFlash('info', 'assessor.enabled.success');
        }

        return $this->redirectToRoute('app_assessor_manager_requests');
    }

    /**
     * @Route("/vote-places", name="app_assessor_manager_vote_places", methods={"GET", "POST"})
     */
    public function votePlacesAction(Request $request, AssessorManager $manager): Response
    {
        try {
            $filters = VotePlaceFilters::fromRequest($request);
        } catch (AssessorException $e) {
            throw new BadRequestHttpException('Unexpected vote place filters in the query string.', $e);
        }

        $user = $this->getUser();

        return $this->render('assessor_manager/vote_places.twig', [
            'votePlaces' => $manager->getVotePlacesProposals($user, $filters),
            'total_count' => $manager->countVotePlacesProposals($user, $filters),
            'filters' => $filters,
        ]);
    }

    /**
     * @Route(
     *     "/vote-places/plus",
     *     name="app_assessor_manager_vote_places_list",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"}
     * )
     */
    public function votePlacesMoreAction(Request $request, AssessorManager $manager): Response
    {
        try {
            $filters = VotePlaceFilters::fromRequest($request);
        } catch (AssessorException $e) {
            throw new BadRequestHttpException('Unexpected vote place filters in the query string.', $e);
        }

        if (!$votePlaces = $manager->getVotePlacesProposals($this->getUser(), $filters)) {
            return new Response();
        }

        return $this->render('assessor_manager/_requests_list.html.twig', [
            'votePlaces' => $votePlaces,
        ]);
    }
}

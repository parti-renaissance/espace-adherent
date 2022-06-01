<?php

namespace App\Controller\EnMarche;

use App\Assessor\AssessorManager;
use App\Assessor\AssessorRequestExporter;
use App\Assessor\Filter\AssessorRequestFilters;
use App\Assessor\Filter\CitiesFilters;
use App\Assessor\Filter\VotePlaceFilters;
use App\Entity\ActionEnum;
use App\Entity\AssessorRequest;
use App\Entity\Election\VotePlace;
use App\Exception\AssessorException;
use App\Exporter\CityAssessorExporter;
use App\Form\ConfirmActionType;
use App\Repository\AssessorRequestRepository;
use App\Repository\Election\VotePlaceRepository;
use App\Serializer\XlsxEncoder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-responsable-assesseur")
 * @Security("is_granted('ROLE_ASSESSOR_MANAGER')")
 */
class AssessorManagerController extends AbstractController
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
     * @Security("is_granted('MANAGE_ASSESSOR', assessorRequest)")
     */
    public function assessorRequestAction(
        AssessorRequest $assessorRequest,
        AssessorManager $manager,
        VotePlaceRepository $repository
    ): Response {
        return $this->render('assessor_manager/request.html.twig', [
            'request' => $assessorRequest,
            'matchingVotePlaces' => $matchingVotePlaces = $repository->findMatchingVotePlaces($assessorRequest),
            'availabilities' => $manager->getOfficeAvailabilities(
                array_map(function (VotePlace $votePlace) {
                    return $votePlace->getId();
                }, array_merge(
                    $matchingVotePlaces,
                    $assessorRequest->getVotePlace() ? [$assessorRequest->getVotePlace()] : []
                ))
            ),
        ]);
    }

    /**
     * @Route(
     *     "/demande/{uuid}/associer/{votePlaceId}",
     *     requirements={"uuid": "%pattern_uuid%", "votePlaceId": "\d+"},
     *     name="app_assessor_manager_request_associate",
     *     methods={"GET", "POST"}
     * )
     * @ParamConverter("assessorRequest", class="App\Entity\AssessorRequest", options={"mapping": {"uuid": "uuid"}})
     * @ParamConverter("votePlace", class="App\Entity\Election\VotePlace", options={"id": "votePlaceId"})
     * @Security("is_granted('MANAGE_ASSESSOR', assessorRequest)")
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
            'availabilities' => $manager->getOfficeAvailabilities([$votePlace->getId()]),
        ]);
    }

    /**
     * @Route(
     *     "/demande/{uuid}/desassocier",
     *     requirements={"uuid": "%pattern_uuid%"},
     *     name="app_assessor_manager_request_deassociate",
     *     methods={"GET", "POST"}
     * )
     * @Security("is_granted('MANAGE_ASSESSOR', assessorRequest)")
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
            'votePlace' => $votePlace = $assessorRequest->getVotePlace(),
            'availabilities' => $manager->getOfficeAvailabilities([$votePlace->getId()]),
        ]);
    }

    /**
     * @Route(
     *     "/transform/{uuid}/{action}",
     *     requirements={"uuid": "%pattern_uuid%", "action": App\Entity\ActionEnum::ACTIONS_URI_REGEX },
     *     name="app_assessor_manager_request_transform",
     *     methods={"GET"}
     * )
     * @Security("is_granted('MANAGE_ASSESSOR', assessorRequest)")
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
            'vote_places' => $paginator = $manager->getVotePlacesProposals($user, $filters),
            'total_count' => $paginator->getTotalItems(),
            'availabilities' => $manager->getOfficeAvailabilities(
                array_map(function (VotePlace $votePlace) {
                    return $votePlace->getId();
                }, iterator_to_array($paginator))
            ),
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

        $votePlaces = $manager->getVotePlacesProposals($this->getUser(), $filters);

        if (!$votePlaces->count()) {
            return new Response();
        }

        return $this->render('assessor_manager/_vote_places_list.html.twig', [
            'vote_places' => $votePlaces,
            'availabilities' => $manager->getOfficeAvailabilities(
                array_map(function (VotePlace $votePlace) {
                    return $votePlace->getId();
                }, iterator_to_array($votePlaces))
            ),
        ]);
    }

    /**
     * @Route(
     *     "/vote-places/export",
     *     name="app_assessor_manager_vote_places_export",
     *     methods={"GET"}
     * )
     */
    public function votePlacesExportAction(
        AssessorRequestRepository $repository,
        AssessorRequestExporter $exporter
    ): Response {
        return new Response(
            $exporter->export(
                $repository->findAllProcessedManagedRequests($this->getUser())
            ),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => sprintf(
                    'attachment;filename=%s.%s',
                    AssessorRequestExporter::FILE_NAME,
                    XlsxEncoder::FORMAT
                ),
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    /**
     * @Route("/communes", name="app_assessor_manager_cities", methods={"GET", "POST"})
     */
    public function citiesAction(Request $request, AssessorManager $manager): Response
    {
        try {
            $filters = CitiesFilters::fromRequest($request);
        } catch (AssessorException $e) {
            throw new BadRequestHttpException('Unexpected cities filters in the query string.', $e);
        }

        $cities = $manager->getVotePlacesCities($this->getUser(), $filters);

        return $this->render('assessor_manager/cities.html.twig', [
            'cities' => $cities,
            'total_count' => \count($cities),
            'filters' => $filters,
        ]);
    }

    /**
     * @Route("/communes/export", name="app_assessor_manager_city_assessors_export", methods={"GET"})
     */
    public function CityAssessorsExporter(
        Request $request,
        CityAssessorExporter $exporter,
        VotePlaceRepository $repository
    ): Response {
        if (!$request->query->has('commune')) {
            throw new BadRequestHttpException('commune parameter is missing');
        }

        $cityCode = $request->query->get('commune');
        if (!$cityCode) {
            return new Response();
        }

        return new Response(
            $exporter->export($repository->findForCityAssessors($this->getUser(), $cityCode)),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => sprintf(
                    'attachment;filename=%s-Assesseurs-%s.%s',
                    $cityCode,
                    (new \DateTime())->format('d-m-Y'),
                    XlsxEncoder::FORMAT
                ),
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}

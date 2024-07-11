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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[IsGranted('ROLE_ASSESSOR_MANAGER')]
#[Route(path: '/espace-responsable-assesseur')]
class AssessorManagerController extends AbstractController
{
    #[Route(name: 'app_assessor_manager_requests', methods: ['GET'])]
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

    #[Route(path: '/plus', name: 'app_assessor_manager_requests_list', condition: 'request.isXmlHttpRequest()', methods: ['GET'])]
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

    #[IsGranted('MANAGE_ASSESSOR', subject: 'assessorRequest')]
    #[Route(path: '/demande/{uuid}', requirements: ['uuid' => '%pattern_uuid%'], name: 'app_assessor_manager_request', methods: ['GET'])]
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

    #[IsGranted('MANAGE_ASSESSOR', subject: 'assessorRequest')]
    #[ParamConverter('assessorRequest', class: AssessorRequest::class, options: ['mapping' => ['uuid' => 'uuid']])]
    #[ParamConverter('votePlace', class: VotePlace::class, options: ['id' => 'votePlaceId'])]
    #[Route(path: '/demande/{uuid}/associer/{votePlaceId}', requirements: ['uuid' => '%pattern_uuid%', 'votePlaceId' => '\d+'], name: 'app_assessor_manager_request_associate', methods: ['GET', 'POST'])]
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

    #[IsGranted('MANAGE_ASSESSOR', subject: 'assessorRequest')]
    #[Route(path: '/demande/{uuid}/desassocier', requirements: ['uuid' => '%pattern_uuid%'], name: 'app_assessor_manager_request_deassociate', methods: ['GET', 'POST'])]
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

    #[IsGranted('MANAGE_ASSESSOR', subject: 'assessorRequest')]
    #[Route(path: '/transform/{uuid}/{action}', requirements: ['uuid' => '%pattern_uuid%', 'action' => ActionEnum::ACTIONS_URI_REGEX], name: 'app_assessor_manager_request_transform', methods: ['GET'])]
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

    #[Route(path: '/vote-places', name: 'app_assessor_manager_vote_places', methods: ['GET', 'POST'])]
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

    #[Route(path: '/vote-places/plus', name: 'app_assessor_manager_vote_places_list', condition: 'request.isXmlHttpRequest()', methods: ['GET'])]
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

    #[Route(path: '/vote-places/export', name: 'app_assessor_manager_vote_places_export', methods: ['GET'])]
    public function votePlacesExportAction(
        AssessorRequestRepository $repository,
        AssessorRequestExporter $exporter
    ): Response {
        return $exporter->export($repository->findAllProcessedManagedRequests($this->getUser()));
    }

    #[Route(path: '/communes', name: 'app_assessor_manager_cities', methods: ['GET', 'POST'])]
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

    #[Route(path: '/communes/export', name: 'app_assessor_manager_city_assessors_export', methods: ['GET'])]
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

        return $exporter->export($cityCode, $repository->findForCityAssessors($this->getUser(), $cityCode));
    }
}

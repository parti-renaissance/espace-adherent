<?php

namespace App\Controller\EnMarche;

use App\Entity\ProcurationProxy;
use App\Entity\ProcurationRequest;
use App\Exception\ProcurationException;
use App\Procuration\Filter\ProcurationProxyProposalFilters;
use App\Procuration\Filter\ProcurationRequestFilters;
use App\Procuration\ProcurationManager;
use App\Repository\ElectionRoundRepository;
use App\Repository\ProcurationRequestRepository;
use Doctrine\DBAL\Driver\DriverException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/espace-responsable-procuration")
 * @Security("is_granted('ROLE_PROCURATION_MANAGER')")
 */
class ProcurationManagerController extends AbstractController
{
    /**
     * @Route(name="app_procuration_manager_requests", methods={"GET"})
     */
    public function requestsAction(
        Request $request,
        ProcurationManager $procurationManager,
        ElectionRoundRepository $electionRoundRepository
    ): Response {
        try {
            $filters = ProcurationRequestFilters::fromQueryString($request);
        } catch (ProcurationException $e) {
            throw new BadRequestHttpException('Unexpected procuration request in the query string.', $e);
        }

        $user = $this->getUser();

        return $this->render('procuration_manager/requests.html.twig', [
            'requests' => $procurationManager->getProcurationRequests($user, $filters),
            'total_count' => $procurationManager->countProcurationRequests($user, $filters),
            'filters' => $filters,
            'election_rounds' => $electionRoundRepository->getUpcomingElectionRounds(),
        ]);
    }

    /**
     * @Route("/plus", name="app_procuration_manager_requests_list", condition="request.isXmlHttpRequest()", methods={"GET"})
     */
    public function requestsMoreAction(Request $request, ProcurationManager $procurationManager): Response
    {
        try {
            $filters = ProcurationRequestFilters::fromQueryString($request);
        } catch (ProcurationException $e) {
            throw new BadRequestHttpException('Unexpected procuration request in the query string.', $e);
        }

        if (!$requests = $procurationManager->getProcurationRequests($this->getUser(), $filters)) {
            return new Response();
        }

        return $this->render('procuration_manager/_requests_list.html.twig', [
            'requests' => $requests,
        ]);
    }

    /**
     * @Route("/mandataires", name="app_procuration_manager_proposals", methods={"GET"})
     */
    public function proposalsAction(
        Request $request,
        ProcurationManager $procurationManager,
        ElectionRoundRepository $electionRoundRepository
    ): Response {
        try {
            $filters = ProcurationProxyProposalFilters::fromQueryString($request);
        } catch (ProcurationException $e) {
            throw new BadRequestHttpException('Unexpected procuration proxy proposal filters in the query string.', $e);
        }

        $user = $this->getUser();

        return $this->render('procuration_manager/proposals.html.twig', [
            'proxies' => $procurationManager->getProcurationProxyProposals($user, $filters),
            'total_count' => $procurationManager->countProcurationProxyProposals($user, $filters),
            'filters' => $filters,
            'election_rounds' => $electionRoundRepository->getUpcomingElectionRounds(),
        ]);
    }

    /**
     * @Route("/mandataires/plus", name="app_procuration_manager_proposals_list", condition="request.isXmlHttpRequest()", methods={"GET"})
     */
    public function proposalsMoreAction(Request $request, ProcurationManager $procurationManager): Response
    {
        try {
            $filters = ProcurationProxyProposalFilters::fromQueryString($request);
        } catch (ProcurationException $e) {
            throw new BadRequestHttpException('Unexpected procuration proxy proposal filters in the query string.', $e);
        }

        if (!$proxies = $procurationManager->getProcurationProxyProposals($this->getUser(), $filters)) {
            return new Response();
        }

        return $this->render('procuration_manager/_proposals_list.html.twig', [
            'proxies' => $proxies,
        ]);
    }

    /**
     * @Route(
     *     "/mandataires/{id}",
     *     requirements={"id": "\d+"},
     *     name="app_procuration_manager_proposal",
     *     methods={"GET"}
     * )
     */
    public function proposal(int $id, ProcurationManager $procurationManager): Response
    {
        if (!$proxy = $procurationManager->getProcurationProxyProposal($id, $this->getUser())) {
            throw $this->createNotFoundException(sprintf('No procuration proposal found for id %d.', $id));
        }

        return $this->render('procuration_manager/proposal.html.twig', [
            'proxy' => $proxy,
        ]);
    }

    /**
     * @Route(
     *     "/mandataires/{id}/{action}",
     *     requirements={ "id": "\d+", "action": App\Entity\ProcurationProxy::ACTIONS_URI_REGEX },
     *     name="app_procuration_manager_proposal_transform",
     *     methods={"GET"}
     * )
     */
    public function proposalTransformAction(int $id, string $action, ProcurationManager $procurationManager): Response
    {
        if (!$proxy = $procurationManager->getProcurationProxyProposal($id, $this->getUser())) {
            throw $this->createNotFoundException(sprintf('No proposal found for id %d.', $id));
        }

        if (ProcurationProxy::ACTION_DISABLE === $action) {
            $procurationManager->disableProcurationProxy($proxy);
            $this->addFlash('info', 'procuration_manager.disabled.success');
        } else {
            $procurationManager->enableProcurationProxy($proxy);
            $this->addFlash('info', 'procuration_manager.enabled.success');
        }

        return $this->redirectToRoute('app_procuration_manager_proposals');
    }

    /**
     * @Route(
     *     "/demande/{id}",
     *     requirements={"id": "\d+"},
     *     name="app_procuration_manager_request",
     *     methods={"GET"}
     * )
     */
    public function requestAction(int $id, ProcurationManager $procurationManager): Response
    {
        if (!$request = $procurationManager->getProcurationRequest($id, $this->getUser())) {
            throw $this->createNotFoundException(sprintf('No procuration request found for id %d.', $id));
        }

        return $this->render('procuration_manager/request.html.twig', [
            'request' => $request,
            'matchingProxies' => $procurationManager->getMatchingProcurationProxies($request),
            'nearMatchingProxies' => $procurationManager->getMatchingProcurationProxiesByOtherCities($request),
        ]);
    }

    /**
     * @Route(
     *     "/demande/{id}/{action}/{csrfToken}",
     *     requirements={"id": "\d+", "action": App\Entity\ProcurationRequest::ACTIONS_URI_REGEX},
     *     name="app_procuration_manager_request_transform",
     *     methods={"GET"}
     * )
     */
    public function requestTransformAction(
        int $id,
        string $action,
        string $csrfToken,
        ProcurationManager $procurationManager
    ): Response {
        if (!$this->isCsrfTokenValid('request_action', $csrfToken)) {
            throw $this->createNotFoundException('Invalid token.');
        }

        if (!$request = $procurationManager->getProcurationRequest($id, $this->getUser())) {
            throw $this->createNotFoundException(sprintf('No request found for id %d.', $id));
        }

        switch ($action) {
            case ProcurationRequest::ACTION_PROCESS:
                $procurationManager->processProcurationRequest($request);
                $this->addFlash('info', 'procuration_manager.process.success');

                break;
            case ProcurationRequest::ACTION_UNPROCESS:
                $procurationManager->unprocessProcurationRequest($request);
                $this->addFlash('info', 'procuration_manager.unprocess.success');

                break;
            case ProcurationRequest::ACTION_ENABLE:
                $procurationManager->enableProcurationRequest($request);
                $this->addFlash('info', 'procuration_manager.enabled.success');

                break;
            case ProcurationRequest::ACTION_DISABLE:
                $procurationManager->disableProcurationRequest($request, 'by_procuration_manager');
                $this->addFlash('info', 'procuration_manager.disabled.success');

                break;
        }

        if (\in_array($action, ProcurationRequest::ACTIVATION_ACTIONS)) {
            return $this->redirectToRoute('app_procuration_manager_requests');
        } else {
            return $this->redirectToRoute('app_procuration_manager_request', ['id' => $id]);
        }
    }

    /**
     * @Route(
     *     "/demande/{id}/associer/{proxyId}",
     *     requirements={"id": "\d+", "proxyId": "\d+"},
     *     name="app_procuration_manager_request_associate",
     *     methods={"GET", "POST"}
     * )
     * @ParamConverter("proxy", class="App\Entity\ProcurationProxy", options={"id": "proxyId"})
     */
    public function requestAssociateAction(
        Request $sfRequest,
        ProcurationRequest $procurationRequest,
        ProcurationProxy $proxy,
        ProcurationManager $procurationManager,
        ProcurationRequestRepository $procurationRequestRepository
    ): Response {
        if (!$procurationRequestRepository->isManagedBy($this->getUser(), $procurationRequest)) {
            throw $this->createAccessDeniedException(sprintf('User is not allowed to manage the request with id %d.', $procurationRequest->getId()));
        }

        if ($proxy->isDisabled() || !$proxy->matchesRequest($procurationRequest)) {
            throw $this->createNotFoundException('No proxy for this request.');
        }

        $form = $this->createForm(FormType::class)
            ->handleRequest($sfRequest)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->tryRedirectCatchingDeadLock(function () use ($procurationRequest, $proxy, $procurationManager) {
                $procurationManager->processProcurationRequest($procurationRequest, $proxy, $this->getUser(), true);
                $this->addFlash('info', 'procuration_manager.associate.success');

                return $this->redirectToRoute('app_procuration_manager_request', ['id' => $procurationRequest->getId()]);
            }, 'app_procuration_manager_request_associate', [
                'id' => $procurationRequest->getId(),
                'proxyId' => $proxy->getId(),
            ]);
        }

        return $this->render('procuration_manager/associate.html.twig', [
            'form' => $form->createView(),
            'request' => $procurationRequest,
            'proxy' => $proxy,
        ]);
    }

    /**
     * @Route(
     *     "/demande/{id}/desassocier",
     *     requirements={"id": "\d+"},
     *     name="app_procuration_manager_request_deassociate",
     *     methods={"GET", "POST"}
     * )
     */
    public function requestDessociateAction(
        Request $sfRequest,
        ProcurationRequest $procurationRequest,
        ProcurationManager $procurationManager,
        ProcurationRequestRepository $repository
    ): Response {
        if (!$procurationRequest->hasFoundProxy()) {
            throw $this->createNotFoundException('This request has no proxy.');
        }

        if (!$repository->isManagedBy($this->getUser(), $procurationRequest)) {
            throw $this->createNotFoundException('Request is not managed by the current user.');
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($sfRequest);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->tryRedirectCatchingDeadLock(function () use ($procurationRequest, $procurationManager) {
                $procurationManager->unprocessProcurationRequest($procurationRequest, $this->getUser(), true);
                $this->addFlash('info', 'procuration_manager.deassociate.success');

                return $this->redirectToRoute('app_procuration_manager_request', ['id' => $procurationRequest->getId()]);
            }, 'app_procuration_manager_request_deassociate', ['id' => $procurationRequest->getId()]);
        }

        return $this->render('procuration_manager/deassociate.html.twig', [
            'form' => $form->createView(),
            'request' => $procurationRequest,
            'proxy' => $procurationRequest->getFoundProxy(),
        ]);
    }

    private function tryRedirectCatchingDeadLock(
        callable $try,
        string $retryRoute,
        array $retryParams
    ): RedirectResponse {
        try {
            return $try();
        } catch (DriverException $e) {
            if (stripos($e->getMessage(), 'deadlock')) {
                // Let the user retry
                $this->addFlash('info', 'procuration_manager.db_error');

                return $this->redirectToRoute($retryRoute, $retryParams);
            }

            throw $e;
        }
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Exception\ProcurationException;
use AppBundle\Procuration\Filter\ProcurationProxyProposalFilters;
use AppBundle\Procuration\Filter\ProcurationRequestFilters;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @Route("/espace-responsable-procuration")
 * @Security("is_granted('ROLE_PROCURATION_MANAGER')")
 */
class ProcurationManagerController extends Controller
{
    /**
     * @Route("", name="app_procuration_manager_index")
     * @Method("GET")
     */
    public function indexAction(Request $request): Response
    {
        try {
            $filters = ProcurationRequestFilters::fromQueryString($request);
        } catch (ProcurationException $e) {
            throw new BadRequestHttpException('Unexpected procuration request type in the query string.', $e);
        }

        $manager = $this->get('app.procuration.manager');

        return $this->render('procuration_manager/requests.html.twig', [
            'requests' => $manager->getProcurationRequests($this->getUser(), $filters),
            'total_count' => $manager->countProcurationRequests($this->getUser(), $filters),
            'filters' => $filters,
        ]);
    }

    /**
     * @Route("/plus", name="app_procuration_manager_requests_list")
     * @Method("GET")
     */
    public function requestsMoreAction(Request $request): Response
    {
        try {
            $filters = $filters = ProcurationRequestFilters::fromQueryString($request);
        } catch (ProcurationException $e) {
            throw new BadRequestHttpException('Unexpected procuration request type in the query string.', $e);
        }

        if (!$requests = $this->get('app.procuration.manager')->getProcurationRequests($this->getUser(), $filters)) {
            return new Response();
        }

        return $this->render('procuration_manager/_requests_list.html.twig', [
            'requests' => $requests,
        ]);
    }

    /**
     * @Route("/mandataires", name="app_procuration_manager_proposals")
     * @Method("GET")
     */
    public function proposalsAction(Request $request): Response
    {
        try {
            $filters = ProcurationProxyProposalFilters::fromQueryString($request);
        } catch (ProcurationException $e) {
            throw new BadRequestHttpException('Unexpected procuration proxy proposal filters in the query string.', $e);
        }

        $manager = $this->get('app.procuration.manager');

        return $this->render('procuration_manager/proposals.html.twig', [
            'proxies' => $manager->getProcurationProxyProposals($this->getUser(), $filters),
            'total_count' => $manager->countProcurationProxyProposals($this->getUser(), $filters),
            'filters' => $filters,
        ]);
    }

    /**
     * @Route("/mandataires/plus", name="app_procuration_manager_proposals_list")
     * @Method("GET")
     */
    public function proposalsMoreAction(Request $request): Response
    {
        try {
            $filters = ProcurationProxyProposalFilters::fromQueryString($request);
        } catch (ProcurationException $e) {
            throw new BadRequestHttpException('Unexpected procuration proxy proposal filters in the query string.', $e);
        }

        if (!$proxies = $this->get('app.procuration.manager')->getProcurationProxyProposals($this->getUser(), $filters)) {
            return new Response();
        }

        return $this->render('procuration_manager/_proposals_list.html.twig', [
            'proxies' => $proxies,
        ]);
    }

    /**
     * @Route(
     *     "/mandataires/{id}/{action}",
     *     requirements={"id"="\d+", "action"="activer|desactiver"},
     *     name="app_procuration_manager_proposal_transform"
     * )
     * @Method("GET")
     */
    public function proposalTransformAction(int $id, string $action): Response
    {
        $manager = $this->get('app.procuration.manager');

        if (!$proxy = $manager->getProcurationProxyProposal($id, $this->getUser())) {
            throw $this->createNotFoundException();
        }

        if ('desactiver' === $action) {
            $manager->disableProcurationProxy($proxy);
            $this->addFlash('info', $this->get('translator')->trans('procuration_manager.disabled.success'));
        } else {
            $manager->enableProcurationProxy($proxy);
            $this->addFlash('info', $this->get('translator')->trans('procuration_manager.enabled.success'));
        }

        return $this->redirectToRoute('app_procuration_manager_proposals');
    }

    /**
     * @Route(
     *     "/demande/{id}",
     *     requirements={"id"="\d+"},
     *     name="app_procuration_manager_request"
     * )
     * @Method("GET")
     */
    public function requestAction(int $id): Response
    {
        $manager = $this->get('app.procuration.manager');
        if (!$request = $manager->getProcurationRequest($id, $this->getUser())) {
            throw $this->createNotFoundException();
        }

        return $this->render('procuration_manager/request.html.twig', [
            'request' => $request,
            'matchingProxies' => $manager->getMatchingProcurationProxies($request),
            'csrfToken' => $this->get('security.csrf.token_manager')->getToken('request_action'),
        ]);
    }

    /**
     * @Route(
     *     "/demande/{id}/{action}/{token}",
     *     requirements={"id"="\d+", "action"="traiter|detraiter"},
     *     name="app_procuration_manager_request_transform"
     * )
     * @Method("GET")
     */
    public function requestTransformAction(int $id, string $action, string $token): Response
    {
        if (!$this->isCsrfTokenValid('request_action', $token)) {
            throw $this->createNotFoundException();
        }

        $manager = $this->get('app.procuration.manager');
        if (!$request = $manager->getProcurationRequest($id, $this->getUser())) {
            throw $this->createNotFoundException();
        }

        if ('traiter' === $action) {
            $manager->processProcurationRequest($request);
            $this->addFlash('info', $this->get('translator')->trans('procuration_manager.process.success'));
        } else {
            $manager->unprocessProcurationRequest($request);
            $this->addFlash('info', $this->get('translator')->trans('procuration_manager.unprocess.success'));
        }

        return $this->redirectToRoute('app_procuration_manager_request', ['id' => $request->getId()]);
    }

    /**
     * @Route(
     *     "/demande/{id}/associer/{proxyId}",
     *     requirements={"id"="\d+"},
     *     name="app_procuration_manager_request_associate"
     * )
     * @Method("GET|POST")
     */
    public function requestAssociateAction(Request $sfRequest, ProcurationRequest $request, int $proxyId): Response
    {
        $manager = $this->getDoctrine()->getManager();

        if (!$manager->getRepository(ProcurationRequest::class)->isManagedBy($this->getUser(), $request)) {
            throw $this->createNotFoundException();
        }

        if (!($proxy = $manager->getRepository(ProcurationProxy::class)->find($proxyId))) {
            throw $this->createNotFoundException();
        }

        if ($proxy->isDisabled() || !$request->isProxyMatching($proxy)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($sfRequest);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.procuration.process_handler')->process($this->getUser(), $request, $proxy);
            $this->addFlash('info', $this->get('translator')->trans('procuration_manager.associate.success'));

            return $this->redirectToRoute('app_procuration_manager_request', ['id' => $request->getId()]);
        }

        return $this->render('procuration_manager/associate.html.twig', [
            'form' => $form->createView(),
            'request' => $request,
            'proxy' => $proxy,
        ]);
    }

    /**
     * @Route(
     *     "/demande/{id}/desassocier",
     *     requirements={"id"="\d+"},
     *     name="app_procuration_manager_request_deassociate"
     * )
     * @Method("GET|POST")
     */
    public function requestDessociateAction(Request $sfRequest, ProcurationRequest $request): Response
    {
        if (!$request->hasFoundProxy()) {
            throw $this->createNotFoundException();
        }

        $manager = $this->getDoctrine()->getManager();

        if (!$manager->getRepository(ProcurationRequest::class)->isManagedBy($this->getUser(), $request)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($sfRequest);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.procuration.process_handler')->unprocess($this->getUser(), $request);
            $this->addFlash('info', $this->get('translator')->trans('procuration_manager.deassociate.success'));

            return $this->redirectToRoute('app_procuration_manager_request', ['id' => $request->getId()]);
        }

        return $this->render('procuration_manager/deassociate.html.twig', [
            'form' => $form->createView(),
            'request' => $request,
            'proxy' => $request->getFoundProxy(),
        ]);
    }
}

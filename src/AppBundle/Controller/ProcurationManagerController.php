<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use AppBundle\Form\ProcurationManagerAssociateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Csrf\CsrfToken;

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
    public function indexAction(): Response
    {
        $requestsRepository = $this->getDoctrine()->getRepository(ProcurationRequest::class);
        $requests = $requestsRepository->findManagedBy($this->getUser());

        return $this->render('procuration_manager/index.html.twig', [
            'requests' => $requests,
            'countToProcess' => count(array_filter($requests, function ($request) {
                return !$request['data']['processed'];
            })),
        ]);
    }

    /**
     * @Route("/mandataires", name="app_procuration_manager_proposals")
     * @Method("GET")
     */
    public function proposalsAction(): Response
    {
        $proxiesRepository = $this->getDoctrine()->getRepository(ProcurationProxy::class);

        return $this->render('procuration_manager/proposals.html.twig', [
            'proxies' => $proxiesRepository->findManagedBy($this->getUser()),
        ]);
    }

    /**
     * @Route(
     *     "/demande/{id}",
     *     requirements={"id"="\d+"},
     *     name="app_procuration_manager_request"
     * )
     * @Method("GET")
     */
    public function requestAction(ProcurationRequest $request): Response
    {
        if (!$this->getDoctrine()->getRepository(ProcurationRequest::class)->isManagedBy($this->getUser(), $request)) {
            throw $this->createNotFoundException();
        }

        $proxiesRepository = $this->getDoctrine()->getRepository(ProcurationProxy::class);
        $csrfToken = $this->get('security.csrf.token_manager')->getToken('request_action');

        return $this->render('procuration_manager/request.html.twig', [
            'request' => $request,
            'matchingProxies' => $proxiesRepository->findMatchingProxies($request),
            'csrfToken' => $csrfToken,
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
    public function requestTransformAction(ProcurationRequest $request, $action, $token): Response
    {
        if (!$this->get('security.csrf.token_manager')->isTokenValid(new CsrfToken('request_action', $token))) {
            throw $this->createNotFoundException();
        }

        $manager = $this->getDoctrine()->getManager();

        if (!$manager->getRepository(ProcurationRequest::class)->isManagedBy($this->getUser(), $request)) {
            throw $this->createNotFoundException();
        }

        if ('traiter' === $action) {
            $request->process();
            $this->addFlash('info', $this->get('translator')->trans('procuration_manager.process.success'));
        } else {
            $request->unprocess();
            $this->addFlash('info', $this->get('translator')->trans('procuration_manager.unprocess.success'));
        }

        $manager->persist($request);
        $manager->flush();

        return $this->redirectToRoute('app_procuration_manager_request', ['id' => $request->getId()]);
    }

    /**
     * @Route(
     *     "/demande/{id}/associer/{proxyId}",
     *     requirements={"id"="\d+", "action"="traiter|detraiter"},
     *     name="app_procuration_manager_request_associate"
     * )
     * @Method("GET|POST")
     */
    public function requestAssociateAction(Request $sfRequest, ProcurationRequest $request, $proxyId): Response
    {
        $manager = $this->getDoctrine()->getManager();

        if (!$manager->getRepository(ProcurationRequest::class)->isManagedBy($this->getUser(), $request)) {
            throw $this->createNotFoundException();
        }

        if (!($proxy = $manager->getRepository(ProcurationProxy::class)->find($proxyId))) {
            throw $this->createNotFoundException();
        }

        if (!$request->isProxyMatching($proxy)) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(ProcurationManagerAssociateType::class);
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
}

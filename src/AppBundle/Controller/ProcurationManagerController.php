<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ProcurationProxy;
use AppBundle\Entity\ProcurationRequest;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

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
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }

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
     * @Route(
     *     "/demande/{id}",
     *     requirements={"id"="\d+"},
     *     name="app_procuration_manager_request"
     * )
     * @Method("GET")
     */
    public function requestAction(ProcurationRequest $request): Response
    {
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }

        if (!$this->getDoctrine()->getRepository(ProcurationRequest::class)->isManagedBy($this->getUser(), $request)) {
            throw $this->createNotFoundException();
        }

        $proxiesRepository = $this->getDoctrine()->getRepository(ProcurationProxy::class);

        return $this->render('procuration_manager/request.html.twig', [
            'request' => $request,
            'matchingProxies' => $proxiesRepository->findMatchingProxies($request),
        ]);
    }

    /**
     * @Route(
     *     "/demande/{id}/{action}",
     *     requirements={"id"="\d+", "action"="traiter|detraiter"},
     *     name="app_procuration_manager_request_transform"
     * )
     * @Method("GET")
     */
    public function requestTransformAction(ProcurationRequest $request, $action): Response
    {
        if (!((bool) $this->getParameter('enable_canary'))) {
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
}

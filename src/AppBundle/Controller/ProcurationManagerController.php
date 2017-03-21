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

        return $this->render('procuration_manager/index.html.twig', [
            'requests' => $requestsRepository->findManagedBy($this->getUser()),
        ]);
    }

    /**
     * @Route("/demande/{id}", name="app_procuration_manager_request")
     * @Method("GET")
     */
    public function requestAction(ProcurationRequest $request): Response
    {
        if (!((bool) $this->getParameter('enable_canary'))) {
            throw $this->createNotFoundException();
        }

        if (!$this->getDoctrine()->getRepository(ProcurationRequest::class)->isManagedBy($this->getUser(), $request)) {
            throw $this->createAccessDeniedException();
        }

        $proxiesRepository = $this->getDoctrine()->getRepository(ProcurationProxy::class);

        return $this->render('procuration_manager/request.html.twig', [
            'request' => $request,
            'matchingProxies' => $proxiesRepository->findMatchingProxies($request),
        ]);
    }
}

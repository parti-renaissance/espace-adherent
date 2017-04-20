<?php

namespace AppBundle\Controller\Legislatives;

use AppBundle\Controller\Traits\CanaryControllerTrait;
use AppBundle\Entity\LegislativeCandidate;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    use CanaryControllerTrait;

    /**
     * @Route("/", name="legislatives_homepage")
     * @Method("GET")
     */
    public function indexAction(): Response
    {
        $this->disableInProduction();

        return $this->render('legislatives/homepage.html.twig', [
            'candidates' => $this->getDoctrine()->getRepository(LegislativeCandidate::class)->findAll(),
        ]);
    }

    /**
     * @Route("/search", name="legislatives_search")
     * @Method("GET")
     */
    public function searchAction(Request $request): JsonResponse
    {
        $this->disableInProduction();

        if (!$request->isXmlHttpRequest()) {
            throw $this->createNotFoundException();
        }

        return new JsonResponse(
            $this->getDoctrine()->getRepository(LegislativeCandidate::class)->filter($request) // @todo
        );
    }
}

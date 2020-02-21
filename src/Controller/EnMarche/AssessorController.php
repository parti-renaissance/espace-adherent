<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Assessor\AssessorRequestHandler;
use AppBundle\Form\AssessorRequestType;
use AppBundle\VotePlace\VotePlaceManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/assesseur")
 */
class AssessorController extends Controller
{
    /**
     * @Route(
     *     path="/demande",
     *     name="app_assessor_request",
     *     methods={"GET|POST"},
     * )
     */
    public function assessorRequest(Request $request, AssessorRequestHandler $assessorResquestHandler): Response
    {
        $assessorRequestCommand = $assessorResquestHandler->start(
            (string) $request->request->get('g-recaptcha-response')
        );
        $transition = $assessorResquestHandler->getCurrentTransition($assessorRequestCommand);

        $form = $this
            ->createForm(AssessorRequestType::class, $assessorRequestCommand, ['transition' => $transition])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($assessorResquestHandler->handle($assessorRequestCommand)) {
                $this->addFlash('info', 'assessor_request.create.success');
            }

            return $this->redirectToRoute('app_assessor_request');
        }

        return $this->render('assessor_request/index.html.twig', [
            'assessorRequest' => $assessorRequestCommand,
            'votePlaceWishesLabels' => $assessorResquestHandler->getVotePlaceWishesLabels($assessorRequestCommand),
            'form' => $form->createView(),
            'transition' => $transition,
        ]);
    }

    /**
     * @Route(
     *     path="/demande/retour",
     *     name="app_assessor_request_back",
     *     methods={"GET"},
     * )
     */
    public function assessorRequestBack(AssessorRequestHandler $assessorResquestHandler): Response
    {
        $assessorResquestHandler->back();

        return $this->redirectToRoute('app_assessor_request');
    }

    /**
     * @Route(
     *     path="/demande/bureaux-de-vote",
     *     name="app_assessor_request_find_vote_places",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"},
     * )
     */
    public function assessorRequestFindVotePlaces(Request $request, VotePlaceManager $votePlaceManager): JsonResponse
    {
        return new JsonResponse($votePlaceManager->getVotePlaceWishesByCountryOrPostalCode(
            $request->query->get('country'), $request->query->get('postalCode'))
        );
    }
}

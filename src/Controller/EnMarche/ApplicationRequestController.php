<?php

namespace App\Controller\EnMarche;

use App\ApplicationRequest\ApplicationRequestHandler;
use App\Entity\ApplicationRequest\RunningMateRequest;
use App\Entity\ApplicationRequest\VolunteerRequest;
use App\Form\ApplicationRequest\RunningMateRequestType;
use App\Form\ApplicationRequest\VolunteerRequestType;
use App\Intl\FranceCitiesBundle;
use App\Utils\AreaUtils;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/appel-a-engagement", name="app_application_request_")
 */
class ApplicationRequestController extends Controller
{
    /**
     * @Route(name="request", methods={"GET", "POST"})
     */
    public function requestAction(Request $request, ApplicationRequestHandler $handler): Response
    {
        $volunteerForm = $this->createForm(VolunteerRequestType::class, new VolunteerRequest())
            ->handleRequest($request)
        ;

        $runningMateForm = $this->createForm(RunningMateRequestType::class, new RunningMateRequest())
            ->handleRequest($request)
        ;

        if ($volunteerForm->isSubmitted() && $volunteerForm->isValid()) {
            $handler->handleVolunteerRequest($volunteerForm->getData());

            return $this->redirectToRoute('app_application_request_confirmation');
        }

        if ($runningMateForm->isSubmitted() && $runningMateForm->isValid()) {
            $handler->handleRunningMateRequest($runningMateForm->getData());

            return $this->redirectToRoute('app_application_request_confirmation');
        }

        return $this->render('application_request/request.html.twig', [
            'volunteer_form' => $volunteerForm->createView(),
            'running_mate_form' => $runningMateForm->createView(),
        ]);
    }

    /**
     * @Route("/city/autocompletion",
     *     name="city_autocomplete",
     *     condition="request.isXmlHttpRequest()",
     *     methods={"GET"}
     * )
     */
    public function cityAutocompleteAction(Request $request): JsonResponse
    {
        if (!$search = $request->query->get('search')) {
            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(FranceCitiesBundle::searchCities($search, 20, AreaUtils::INSEE_CODES_ATTACHED_TO_ANNECY));
    }

    /**
     * @Route("/merci", name="confirmation", methods={"GET"})
     */
    public function confirmAction(): Response
    {
        return $this->render('application_request/confirmation.html.twig');
    }
}

<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\ApplicationRequest\ApplicationRequestHandler;
use AppBundle\Entity\ApplicationRequest\RunningMateRequest;
use AppBundle\Entity\ApplicationRequest\VolunteerRequest;
use AppBundle\Form\ApplicationRequest\RunningMateType;
use AppBundle\Form\ApplicationRequest\VolunteerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/appel-a-engagement", name="app_application_request_")
 */
class ApplicationRequestController extends Controller
{
    /**
     * @Route(name="volunteer", methods={"GET", "POST"})
     */
    public function requestAction(Request $request, ApplicationRequestHandler $handler): Response
    {
        $volunteerForm = $this->createForm(VolunteerType::class, new VolunteerRequest())
            ->handleRequest($request);

        $runningMateForm = $this->createForm(RunningMateType::class, new RunningMateRequest())
            ->handleRequest($request);

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
     * @Route("/merci", name="confirmation", methods={"GET"})
     */
    public function confirmAction(): Response
    {
        return $this->render('application_request/confirmation.html.twig');
    }
}

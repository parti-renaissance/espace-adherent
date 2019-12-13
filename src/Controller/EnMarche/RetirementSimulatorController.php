<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Form\RetirementSimulatorType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/retraite-interactif")
 */
class RetirementSimulatorController extends Controller
{
    /**
     * @Route(name="app_retirement_simulator_home", methods={"GET"})
     */
    public function approachesAction(): Response
    {
        $form = $this->createForm(RetirementSimulatorType::class);

        return $this->render('retirement_simulator/home.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\EnMarche;

use App\Form\RetirementSimulatorType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/retraite-interactif')]
class RetirementSimulatorController extends AbstractController
{
    #[Route(name: 'app_retirement_simulator_home', methods: ['GET'])]
    public function approachesAction(): Response
    {
        $form = $this->createForm(RetirementSimulatorType::class);

        return $this->render('retirement_simulator/home.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

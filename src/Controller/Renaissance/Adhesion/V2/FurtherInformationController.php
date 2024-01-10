<?php

namespace App\Controller\Renaissance\Adhesion\V2;

use App\Adhesion\AdhesionStepEnum;
use App\Entity\Adherent;
use App\Form\AdhesionFurtherInformationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/adhesion/informations-complementaires', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
class FurtherInformationController extends AbstractController
{
    public const ROUTE_NAME = 'app_adhesion_further_information';

    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        $adherent = $this->getUser();
        if (!$adherent instanceof Adherent) {
            return $this->redirectToRoute(AdhesionController::ROUTE_NAME);
        }

        if ($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::FURTHER_INFORMATION)) {
            return $this->redirectToRoute('app_renaissance_adherent_space');
        }

        $form = $this
            ->createForm(AdhesionFurtherInformationType::class, $adherent, ['validation_groups' => ['adhesion:further_information']])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $adherent->finishAdhesionStep(AdhesionStepEnum::FURTHER_INFORMATION);
            $entityManager->flush();

            return $this->redirectToRoute(CommitteeController::ROUTE_NAME);
        }

        return $this->renderForm('renaissance/adhesion/further_information.html.twig', [
            'form' => $form,
        ]);
    }
}

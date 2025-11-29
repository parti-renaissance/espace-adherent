<?php

declare(strict_types=1);

namespace App\Controller\BesoinDEurope\Inscription;

use App\Adhesion\AdhesionStepEnum;
use App\Entity\Adherent;
use App\Form\AdhesionFurtherInformationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/inscription/informations-complementaires', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
class FurtherInformationController extends AbstractController
{
    public const ROUTE_NAME = 'app_bde_further_information';

    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        $adherent = $this->getUser();
        if (!$adherent instanceof Adherent) {
            return $this->redirectToRoute(InscriptionController::ROUTE_NAME);
        }

        if ($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::FURTHER_INFORMATION)) {
            return $this->redirectToRoute('vox_app_redirect');
        }

        $form = $this
            ->createForm(AdhesionFurtherInformationType::class, $adherent, [
                'validation_groups' => ['adhesion:further_information'],
                'with_jam_notification' => false,
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $adherent->finishAdhesionStep(AdhesionStepEnum::FURTHER_INFORMATION);
            $entityManager->flush();

            return $this->redirectToRoute(CommunicationReminderController::ROUTE_NAME);
        }

        return $this->renderForm('besoindeurope/inscription/further_information.html.twig', [
            'form' => $form,
        ]);
    }
}

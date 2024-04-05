<?php

namespace App\Controller\BesoinDEurope\Inscription;

use App\Adhesion\ActivationCodeManager;
use App\Adhesion\AdhesionStepEnum;
use App\Entity\Adherent;
use App\Form\ConfirmActionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ActivateEmailController extends AbstractController
{
    public const ROUTE_NAME = 'app_bde_confirm_email';

    #[Route(path: '/confirmation-email', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
    public function validateAction(): Response
    {
        $adherent = $this->getUser();
        if (!$adherent instanceof Adherent) {
            return $this->redirectToRoute(InscriptionController::ROUTE_NAME);
        }

        $this->addFlash('success', 'Votre compte vient d’être créé');

        if ($adherent->hasFinishedAdhesionStep(AdhesionStepEnum::ACTIVATION)) {
            return $this->redirectToRoute('besoindeurope_site');
        }

        return $this->renderForm('besoindeurope/inscription/confirmation_email.html.twig', [
            'code_ttl' => ActivationCodeManager::CODE_TTL,
            'new_code_form' => $this->createForm(ConfirmActionType::class, null, ['with_deny' => false, 'allow_label' => 'Renvoyer le code']),
        ]);
    }
}

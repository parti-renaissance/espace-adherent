<?php

namespace App\Controller\Renaissance\Payment;

use App\Adhesion\AdhesionStepEnum;
use App\Entity\Donation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/paiement/{uuid}/statut', name: 'app_payment_check', methods: ['GET'])]
class CheckStatusController extends AbstractController
{
    public function __invoke(Request $request, Donation $donation): Response
    {
        $isSuccess = false;
        if ($request->getSession()->get(StatusController::SESSION_KEY) === $donation->getUuid()->toString() && $donation->isSuccess()) {
            $isSuccess = true;

            if ($donation->isMembership()) {
                if (!$donation->isReAdhesion() && !$donation->getDonator()?->getAdherent()?->hasFinishedAdhesionStep(AdhesionStepEnum::ACTIVATION)) {
                    $this->addFlash('success', 'Votre paiement a bien été validé !');
                    $redirectUri = $this->generateUrl('app_adhesion_confirm_email');
                } else {
                    $redirectUri = $this->generateUrl('app_adhesion_finish');
                }
            }
        }

        return $this->json(['is_success' => $isSuccess, 'redirect_uri' => $redirectUri ?? '']);
    }
}

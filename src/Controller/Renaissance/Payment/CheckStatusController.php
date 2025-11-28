<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Payment;

use App\Controller\Renaissance\Adhesion\ActivateEmailController;
use App\Entity\Donation;
use App\Utils\UtmParams;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/paiement/{uuid}/statut', name: 'app_payment_check', methods: ['GET'])]
class CheckStatusController extends AbstractController
{
    public function __invoke(Request $request, Donation $donation): Response
    {
        $isSuccess = false;
        if ($request->getSession()->get(StatusController::SESSION_KEY) === $donation->getUuid()->toString() && $donation->isSuccess()) {
            $isSuccess = true;
            $params = UtmParams::mergeParams([], $donation->utmSource, $donation->utmCampaign);

            if ($donation->isMembership()) {
                if (!$donation->isReAdhesion()) {
                    $this->addFlash('success', 'Votre paiement a bien été validé !');
                    $redirectUri = $this->generateUrl(ActivateEmailController::ROUTE_NAME, $params);
                } else {
                    $redirectUri = $this->generateUrl('app_adhesion_finish', $params);
                }
            } else {
                $redirectUri = $this->generateUrl('app_donation_finish', $params);
            }
        }

        return $this->json(['is_success' => $isSuccess, 'redirect_uri' => $redirectUri ?? '']);
    }
}

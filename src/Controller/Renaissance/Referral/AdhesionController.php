<?php

namespace App\Controller\Renaissance\Referral;

use App\Entity\Referral;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invitation/adhesion/{identifier}', name: self::ROUTE_NAME, requirements: ['identifier' => 'P[A-Z0-9]{5}'], methods: ['GET', 'POST'])]
class AdhesionController extends AbstractController
{
    public const ROUTE_NAME = 'app_referral_adhesion';

    public function __invoke(Referral $referral): Response
    {
        if (!$referral->isAdhesion() || !$referral->isInProgress()) {
            return $this->redirectToRoute('app_adhesion_index');
        }

        if ($referral->isInvitation()) {
            return $this->redirectToRoute('app_adhesion_with_invitation', ['identifier' => $referral->identifier]);
        }

        return $this->render('renaissance/referral/adhesion.html.twig', [
            'referral' => $referral,
        ]);
    }
}

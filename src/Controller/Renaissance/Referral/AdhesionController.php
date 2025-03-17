<?php

namespace App\Controller\Renaissance\Referral;

use App\Entity\Referral;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invitation/adhesion/{identifier}', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
class AdhesionController extends AbstractController
{
    public const ROUTE_NAME = 'app_referral_adhesion';

    public function __invoke(Referral $referral): Response
    {
        if (!$referral->isAdhesion()) {
            throw $this->createNotFoundException();
        }

        return $this->render('renaissance/referral/adhesion.html.twig', [
            'referral' => $referral,
        ]);
    }
}

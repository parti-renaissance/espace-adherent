<?php

namespace App\Controller\Renaissance\Referral\Report;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/referral/report/confirmation', name: self::ROUTE_NAME, methods: ['GET'])]
class ConfirmationController extends AbstractController
{
    public const ROUTE_NAME = 'app_referral_report_confirmation';

    public function __invoke(): Response
    {
        return $this->render('renaissance/referral/report/confirmation.html.twig');
    }
}

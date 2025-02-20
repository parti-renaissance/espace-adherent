<?php

namespace App\Controller\Renaissance\Referral;

use App\Entity\Referral;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/referral/{uuid}/report', name: self::ROUTE_NAME, requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET', 'POST'])]
class ReportController extends AbstractController
{
    public const ROUTE_NAME = 'app_referral_report';

    public function __invoke(Request $request, Referral $referral): Response
    {
        return $this->render('renaissance/referral/report.html.twig');
    }
}

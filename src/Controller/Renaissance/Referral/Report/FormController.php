<?php

namespace App\Controller\Renaissance\Referral\Report;

use App\Adherent\Referral\ReportHandler;
use App\Entity\Referral;
use App\Form\ConfirmActionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invitation/{identifier}/signaler', name: self::ROUTE_NAME, requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET', 'POST'])]
class FormController extends AbstractController
{
    public const ROUTE_NAME = 'app_referral_report';

    public function __construct(private readonly ReportHandler $reportHandler)
    {
    }

    public function __invoke(Request $request, Referral $referral): Response
    {
        if ($referral->isReported()) {
            return $this->render('renaissance/referral/report/already_reported.html.twig');
        }

        $form = $this
            ->createForm(ConfirmActionType::class, null, [
                'with_deny' => false,
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reportHandler->report($referral);

            return $this->redirectToRoute('app_referral_report_confirmation');
        }

        return $this->render('renaissance/referral/report/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

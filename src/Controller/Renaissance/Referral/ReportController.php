<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Referral;

use App\Adherent\Referral\ReportHandler;
use App\Adherent\Referral\StatusEnum;
use App\Entity\Referral;
use App\Form\ConfirmActionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invitation/{uuid}/signaler', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
class ReportController extends AbstractController
{
    public const ROUTE_NAME = 'app_referral_report';

    public function __invoke(Request $request, Referral $referral, ReportHandler $reportHandler): Response
    {
        if (!$referral->isReportable()) {
            throw $this->createNotFoundException('Cette invitation n\'existe pas.');
        }

        if (StatusEnum::INVITATION_SENT !== $referral->status) {
            return $this->render('renaissance/referral/report.html.twig', [
                'referral' => $referral,
            ]);
        }

        $form = $this
            ->createForm(ConfirmActionType::class, null, ['with_deny' => false])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $reportHandler->report($referral);

            return $this->redirectToRoute(self::ROUTE_NAME, ['uuid' => $referral->getUuid()]);
        }

        return $this->render('renaissance/referral/report.html.twig', [
            'form' => $form->createView(),
            'referral' => $referral,
        ]);
    }
}

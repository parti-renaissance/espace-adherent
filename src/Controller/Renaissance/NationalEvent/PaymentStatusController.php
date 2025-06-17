<?php

namespace App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\NationalEvent\Payment\RequestParamsBuilder;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{slug}/{uuid}/paiement/statut', name: 'app_national_event_payment_status', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
class PaymentStatusController extends AbstractController
{
    public function __invoke(
        Request $request,
        string $app_domain,
        #[MapEntity(mapping: ['slug' => 'slug'])] NationalEvent $event,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] EventInscription $inscription,
        RequestParamsBuilder $requestParamsBuilder,
    ): Response {
        $status = $request->query->get('status', 'unknown');

        if ('success' === $status) {
            return $this->redirectToRoute('app_national_event_inscription_confirmation', ['slug' => $event->getSlug()]);
        }

        return $this->render('renaissance/national_event/payment_status.html.twig', [
            'event' => $event,
            'inscription' => $inscription,
            'status' => $status,
        ]);
    }
}

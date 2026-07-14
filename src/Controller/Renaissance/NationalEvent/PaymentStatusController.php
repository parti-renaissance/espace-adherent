<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\NationalEvent;

use App\Analytics\PostHog\Events\PostHogEventName;
use App\Analytics\PostHog\PostHogService;
use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\NationalEvent\Payment\RequestParamsBuilder;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Service\Attribute\Required;

#[Route('/{slug}/{uuid}/paiement/statut', name: 'app_national_event_payment_status', requirements: ['slug' => '[^/]+', 'uuid' => '%pattern_uuid%'], methods: ['GET'])]
class PaymentStatusController extends AbstractController
{
    private PostHogService $postHog;

    #[Required]
    public function setPostHogService(PostHogService $postHog): void
    {
        $this->postHog = $postHog;
    }

    public function __invoke(
        Request $request,
        string $app_domain,
        #[MapEntity(mapping: ['slug' => 'slug'])] NationalEvent $event,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] EventInscription $inscription,
        RequestParamsBuilder $requestParamsBuilder,
    ): Response {
        $status = $request->query->get('status', 'unknown');

        if ('success' === $status) {
            // Cas 1 forcé (spec §8.4) — pas de $set.email PostHog.
            // payment_method non disponible sur l'entité Payment (todo Fontaine Phase 1.5 :
            // ajouter un champ payment_method sur Payment + passer la valeur ici).
            $this->postHog->captureServerSide(
                PostHogEventName::NATIONAL_EVENT_PAYMENT_COMPLETED,
                [
                    'event_uuid' => $event->getUuid()->toRfc4122(),
                    'inscription_uuid' => $inscription->getUuid()->toRfc4122(),
                    'amount_eur' => $inscription->getAmountInEuro(),
                    'payment_method' => null, // todo Fontaine: Payment::$paymentMethod manquant
                ],
                $inscription->adherent,
            );

            if ($event->isPackageEventType()) {
                return $this->redirectToRoute('app_national_event_my_inscription', ['slug' => $event->getSlug(), 'uuid' => $inscription->getUuid()->toRfc4122(), 'app_domain' => $app_domain, 'confirmation' => true]);
            }

            return $this->redirectToRoute('app_national_event_inscription_confirmation', ['slug' => $event->getSlug()]);
        }

        return $this->render('renaissance/national_event/payment_status.html.twig', [
            'event' => $event,
            'inscription' => $inscription,
            'status' => $status,
        ]);
    }
}

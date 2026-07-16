<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Entity\NationalEvent\Payment;
use App\NationalEvent\Payment\InscriptionRedirectionBuilder;
use App\Repository\NationalEvent\PaymentRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

/**
 * Polled by the payment status page while the Paybox IPN is still in flight. Read-only on purpose: the status it
 * reports is only ever written by the IPN, which is what makes polling safe to expose.
 *
 * The response shape matches app_payment_check so the generic payment_status.js module can be reused as-is.
 */
#[Route('/{slug}/{uuid}/paiement/verification', name: 'app_national_event_payment_check', requirements: ['slug' => '[^/]+', 'uuid' => '%pattern_uuid%'], methods: ['GET'])]
class PaymentCheckController extends AbstractController
{
    public function __invoke(
        Request $request,
        string $app_domain,
        #[MapEntity(mapping: ['slug' => 'slug'])] NationalEvent $event,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] EventInscription $inscription,
        PaymentRepository $paymentRepository,
        InscriptionRedirectionBuilder $redirectionBuilder,
    ): Response {
        $payment = $this->findReturnedPayment($request, $inscription, $paymentRepository);
        $isSuccess = $payment instanceof Payment && $payment->isConfirmed();

        return $this->json([
            'is_success' => $isSuccess,
            'redirect_uri' => $isSuccess ? $redirectionBuilder->buildConfirmationUrl($inscription, $app_domain) : '',
        ]);
    }

    private function findReturnedPayment(Request $request, EventInscription $inscription, PaymentRepository $paymentRepository): ?Payment
    {
        $paymentUuid = $request->query->get('payment');

        if (!\is_string($paymentUuid) || !Uuid::isValid($paymentUuid)) {
            return null;
        }

        $payment = $paymentRepository->findOneByUuid($paymentUuid);

        return $payment instanceof Payment && $payment->inscription === $inscription ? $payment : null;
    }
}

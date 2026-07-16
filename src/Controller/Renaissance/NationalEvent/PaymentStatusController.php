<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Entity\NationalEvent\Payment;
use App\Entity\Transaction;
use App\NationalEvent\Payment\InscriptionRedirectionBuilder;
use App\NationalEvent\Payment\Worldline\CheckoutOutcomeResolver;
use App\NationalEvent\PaymentStatusEnum;
use App\Repository\NationalEvent\PaymentRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/{slug}/{uuid}/paiement/statut', name: 'app_national_event_payment_status', requirements: ['slug' => '[^/]+', 'uuid' => '%pattern_uuid%'], methods: ['GET'])]
class PaymentStatusController extends AbstractController
{
    public function __invoke(
        Request $request,
        string $app_domain,
        #[MapEntity(mapping: ['slug' => 'slug'])] NationalEvent $event,
        #[MapEntity(mapping: ['uuid' => 'uuid'])] EventInscription $inscription,
        PaymentRepository $paymentRepository,
        CheckoutOutcomeResolver $checkoutOutcomeResolver,
        InscriptionRedirectionBuilder $redirectionBuilder,
    ): Response {
        $status = $request->query->get('status', 'unknown');
        $paymentCheckUrl = null;

        if ($payment = $this->findReturnedPayment($request, $inscription, $paymentRepository)) {
            // The rail is carried by the payment itself: a Paybox payment has no checkout session to resolve.
            if (null !== $payment->donation) {
                $status = $this->toPayboxViewStatus($request, $payment);

                if ('pending' === $status) {
                    $paymentCheckUrl = $this->generateUrl('app_national_event_payment_check', [
                        'slug' => $event->getSlug(),
                        'uuid' => $inscription->getUuid()->toRfc4122(),
                        'app_domain' => $app_domain,
                        'payment' => $payment->getUuid()->toRfc4122(),
                    ]);
                }
            } else {
                $status = $this->toViewStatus($checkoutOutcomeResolver->resolve($payment));
            }
        }

        if ('success' === $status) {
            return $this->redirect($redirectionBuilder->buildConfirmationUrl($inscription, $app_domain));
        }

        return $this->render('renaissance/national_event/payment_status.html.twig', [
            'event' => $event,
            'inscription' => $inscription,
            'status' => $status,
            'payment_check_url' => $paymentCheckUrl,
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

    /**
     * The Paybox result code only decides what to display, never whether the payment is real: a forged success makes
     * the page poll for a confirmation that will never come, and a forged failure only misleads its own author.
     */
    private function toPayboxViewStatus(Request $request, Payment $payment): string
    {
        if ($payment->isConfirmed()) {
            return 'success';
        }

        // Only a payment the bank accepted is worth waiting for: polling a cancelled one would spin for nothing.
        if (Transaction::PAYBOX_SUCCESS === $request->query->get('result')) {
            return 'pending';
        }

        return 'error';
    }

    private function toViewStatus(PaymentStatusEnum $status): string
    {
        return match ($status) {
            PaymentStatusEnum::CONFIRMED => 'success',
            PaymentStatusEnum::PENDING => 'pending',
            default => 'error',
        };
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\NationalEvent;

use App\Entity\NationalEvent\EventInscription;
use App\Entity\NationalEvent\NationalEvent;
use App\Entity\NationalEvent\Payment;
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
    ): Response {
        $status = $request->query->get('status', 'unknown');

        if ($payment = $this->findReturnedPayment($request, $inscription, $paymentRepository)) {
            $status = $this->toViewStatus($checkoutOutcomeResolver->resolve($payment));
        }

        if ('success' === $status) {
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

    private function findReturnedPayment(Request $request, EventInscription $inscription, PaymentRepository $paymentRepository): ?Payment
    {
        $paymentUuid = $request->query->get('payment');

        if (!\is_string($paymentUuid) || !Uuid::isValid($paymentUuid)) {
            return null;
        }

        $payment = $paymentRepository->findOneByUuid($paymentUuid);

        return $payment instanceof Payment && $payment->inscription === $inscription ? $payment : null;
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

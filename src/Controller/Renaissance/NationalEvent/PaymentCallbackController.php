<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\NationalEvent;

use App\Donation\Request\DonationRequestUtils;
use App\Entity\NationalEvent\Payment;
use App\Repository\DonationRepository;
use App\Repository\NationalEvent\PaymentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

/**
 * Where Paybox sends the payer back. It never applies a status: the browser return carries no Paybox signature, so
 * only the IPN may confirm a payment. This just routes the payer to the inscription status page.
 */
#[Route(path: '/paiement/callback-inscription/{_callback_token}', name: 'app_national_event_payment_callback', requirements: ['_callback_token' => '.+'], methods: ['GET'])]
class PaymentCallbackController extends AbstractController
{
    public function __invoke(
        Request $request,
        string $app_domain,
        string $_callback_token,
        DonationRepository $donationRepository,
        PaymentRepository $paymentRepository,
        DonationRequestUtils $donationRequestUtils,
    ): Response {
        $payment = $this->findPayment($request, $donationRepository, $paymentRepository);

        // Without a resolvable payment there is no inscription to send the payer back to: every national event route
        // is scoped by event slug. An unresolvable "id" is not a legitimate payer flow anyway.
        if (!$payment instanceof Payment) {
            throw $this->createNotFoundException('No national event payment matches this Paybox callback.');
        }

        $payload = $donationRequestUtils->extractPayboxResultFromCallback($request, $_callback_token);
        $inscription = $payment->inscription;

        return $this->redirectToRoute('app_national_event_payment_status', [
            'slug' => $inscription->event->getSlug(),
            'uuid' => $inscription->getUuid()->toRfc4122(),
            'app_domain' => $app_domain,
            'payment' => $payment->getUuid()->toRfc4122(),
            'result' => $payload['result'],
        ]);
    }

    private function findPayment(
        Request $request,
        DonationRepository $donationRepository,
        PaymentRepository $paymentRepository,
    ): ?Payment {
        // Paybox echoes PBX_CMD back in "id", built as "<donation uuid>_<slug>".
        $donationUuid = explode('_', (string) $request->query->get('id'))[0];

        if (!$donationUuid || !Uuid::isValid($donationUuid)) {
            return null;
        }

        if (!$donation = $donationRepository->findOneByUuid($donationUuid)) {
            return null;
        }

        return $paymentRepository->findOneBy(['donation' => $donation]);
    }
}

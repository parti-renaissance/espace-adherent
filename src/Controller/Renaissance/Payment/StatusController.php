<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Payment;

use App\Donation\Request\DonationRequestUtils;
use App\Entity\Transaction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/paiement', name: 'app_payment_status', methods: ['GET'])]
class StatusController extends AbstractController
{
    public const SESSION_KEY = 'donation_uuid';

    public function __invoke(Request $request, DonationRequestUtils $donationRequestUtils): Response
    {
        $resultCode = $request->query->get('result');

        if ($uuid = $request->query->get('uuid')) {
            if (Transaction::PAYBOX_SUCCESS === $resultCode) {
                $request->getSession()->set(self::SESSION_KEY, $uuid);
            } else {
                $retryUrl = $this->generateUrl('app_payment_retry', [
                    'uuid' => $uuid,
                    '_retry_token' => $donationRequestUtils->generateRetryToken(),
                ]);
            }
        }

        return $this->render('renaissance/payment/status.html.twig', [
            'result_code' => $resultCode,
            'uuid' => $uuid,
            'retry_url' => $retryUrl ?? null,
        ]);
    }
}

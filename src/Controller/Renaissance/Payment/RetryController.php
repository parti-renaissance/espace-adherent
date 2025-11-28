<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Payment;

use App\Donation\Handler\DonationRequestHandler;
use App\Donation\Request\DonationRequestUtils;
use App\Entity\Donation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/paiement/retry/{uuid}', name: 'app_payment_retry', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
class RetryController extends AbstractController
{
    public function __invoke(Request $request, DonationRequestUtils $donationRequestUtils, DonationRequestHandler $donationRequestHandler, Donation $donation): Response
    {
        if (
            !($retryToken = $request->query->get('_retry_token'))
            || !$donationRequestUtils->validateRetryToken($retryToken)
        ) {
            $this->addFlash('error', 'Le jeton n\'est pas invalide.');

            return $this->redirectToRoute('app_payment_status');
        }

        $newDonation = $donationRequestHandler->handleRetry($donation);

        return $this->redirectToRoute('app_payment', ['uuid' => $newDonation->getUuid()]);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Payment;

use App\Donation\Paybox\PayboxFormFactory;
use App\Donation\Request\DonationRequestUtils;
use App\Entity\Donation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/paiement/{uuid}', name: 'app_payment', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
class PaymentController extends AbstractController
{
    public function __invoke(Request $request, DonationRequestUtils $donationRequestUtils, PayboxFormFactory $payboxFormFactory, Donation $donation): Response
    {
        $paybox = $payboxFormFactory->createPayboxFormForDonation($donation, 'app_payment_callback');

        return $this->render('renaissance/payment/payment.html.twig', [
            'url' => $paybox->getUrl(),
            'form' => $paybox->getForm()->createView(),
        ]);
    }
}

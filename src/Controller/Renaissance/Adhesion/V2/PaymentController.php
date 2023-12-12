<?php

namespace App\Controller\Renaissance\Adhesion\V2;

use App\Donation\Paybox\PayboxFormFactory;
use App\Entity\Donation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v2/adhesion/{uuid}/paiement', name: 'app_adhesion_payment', requirements: ['uuid' => '%pattern_uuid%'], methods: ['GET'])]
class PaymentController extends AbstractController
{
    public function __invoke(PayboxFormFactory $payboxFormFactory, Donation $donation): Response
    {
        $paybox = $payboxFormFactory->createPayboxFormForDonation($donation);

        return $this->render('renaissance/adhesion/payment_v2.html.twig', [
            'url' => $paybox->getUrl(),
            'form' => $paybox->getForm()->createView(),
        ]);
    }
}

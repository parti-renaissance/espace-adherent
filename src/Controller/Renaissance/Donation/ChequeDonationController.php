<?php

namespace App\Controller\Renaissance\Donation;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/don/cheque", name="app_renaissance_donation_cheque", methods={"GET"})
 */
class ChequeDonationController extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('renaissance/donation/cheque_payment.html.twig');
    }
}

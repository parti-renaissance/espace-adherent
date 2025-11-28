<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Donation\V2;

use App\Controller\Renaissance\Payment\StatusController;
use App\Repository\DonationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/don/merci', name: 'app_donation_finish', methods: ['GET'])]
class FinishController extends AbstractController
{
    public function __invoke(Request $request, DonationRepository $donationRepository): Response
    {
        if (
            !($lastDonationUuid = $request->getSession()->get(StatusController::SESSION_KEY))
            || !($donation = $donationRepository->findOneByUuid($lastDonationUuid))
        ) {
            return $this->redirectToRoute('app_donation_index');
        }

        return $this->render('renaissance/donation/finish.html.twig', ['donation' => $donation]);
    }
}

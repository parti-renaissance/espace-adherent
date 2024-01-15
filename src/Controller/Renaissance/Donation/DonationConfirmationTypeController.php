<?php

namespace App\Controller\Renaissance\Donation;

use App\Form\Renaissance\Donation\DonationRequestConfirmType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v1/don/confirmation-type-de-don', name: 'app_renaissance_donation_confirmation_type', methods: ['GET|POST'])]
class DonationConfirmationTypeController extends AbstractDonationController
{
    public function __invoke(Request $request): Response
    {
        $command = $this->getCommand();

        if (!$this->processor->canChangeDonationType($command)) {
            return $this->redirectToRoute('app_renaissance_donation');
        }

        $this->processor->doChangeDonationType($command);

        $form = $this
            ->createForm(DonationRequestConfirmType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processor->doFillPersonalInfo($command);

            return $this->redirectToRoute('app_renaissance_donation_informations');
        }

        return $this->render('renaissance/donation/confirm_type.html.twig', [
            'donation' => $command,
            'form' => $form->createView(),
        ]);
    }
}

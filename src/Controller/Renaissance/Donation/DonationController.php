<?php

namespace App\Controller\Renaissance\Donation;

use App\Donation\DonationRequestUtils;
use App\Form\Renaissance\Donation\DonationRequestAmountType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/don", name="app_renaissance_donation", methods={"GET|POST"})
 */
class DonationController extends AbstractDonationController
{
    public function __invoke(Request $request, DonationRequestUtils $donationRequestUtils): Response
    {
        $command = $this->getCommand();

        if (!$this->processor->canChooseDonationAmount($command)) {
            return $this->redirectToRoute('app_renaissance_homepage');
        }

        $this->processor->doChooseDonationAmount($command);

        $form = $this
            ->createForm(DonationRequestAmountType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($donationRequestUtils->hasAmountAlert($command->getAmount(), $command->getDuration())) {
                $this->processor->doChangeDonationType($command);

                return $this->redirectToRoute('app_renaissance_donation_confirmation_type');
            }

            $this->processor->doFillPersonalInfo($command);

            return $this->redirectToRoute('app_renaissance_donation_informations');
        }

        return $this->render('renaissance/donation/choose_donation_amount.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

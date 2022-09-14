<?php

namespace App\Controller\Renaissance\Donation;

use App\Donation\DonationRequestUtils;
use App\Donation\PayboxPaymentSubscription;
use App\Exception\InvalidPayboxPaymentSubscriptionValueException;
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

        if (
            $request->isMethod(Request::METHOD_POST)
            && (
                $request->request->has('montant')
                && $request->request->get('montant')
                && is_numeric($request->request->get('montant'))
            )
        ) {
            $duration = $request->request->getInt('abonnement') ? PayboxPaymentSubscription::UNLIMITED : PayboxPaymentSubscription::NONE;
            $amount = $request->request->get('montant');

            if (!PayboxPaymentSubscription::isValid($duration)) {
                throw new InvalidPayboxPaymentSubscriptionValueException($duration);
            }

            $command->setDuration($duration);
            $command->setAmount((float) $amount);

            if ($donationRequestUtils->hasAmountAlert($amount, $duration)) {
                $this->processor->doChangeDonationType($command);

                return $this->redirectToRoute('app_renaissance_donation_confirmation_type');
            }

            $this->processor->doFillPersonalInfo($command);

            return $this->redirectToRoute('app_renaissance_donation_informations');
        }

        return $this->render('renaissance/donation/choose_donation_amount.html.twig');
    }
}

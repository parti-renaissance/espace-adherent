<?php

namespace App\Controller\Renaissance\Donation;

use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Donation\Request\DonationRequestUtils;
use App\Form\Renaissance\Donation\DonationRequestDonatorType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v1/don/coordonnees', name: 'app_renaissance_donation_informations', methods: ['GET|POST'])]
class DonatorInfoController extends AbstractDonationController
{
    private const RETRY_PAYLOAD = 'donation_retry_payload';

    public function __invoke(Request $request, DonationRequestUtils $donationRequestUtils): Response
    {
        $command = $this->getCommand();

        if (!$this->processor->canFillPersonalInfo($command)) {
            return $this->redirectToRoute('app_donation_index');
        }

        $this->processor->doFillPersonalInfo($command);

        if ($request->query->has(self::RETRY_PAYLOAD)) {
            $command = $donationRequestUtils->hydrateFromRetryPayload($command, $request->query->get(self::RETRY_PAYLOAD, '{}'));

            if ($request->query->has('montant') && $request->query->has('montant')) {
                $command->setAmount((float) $request->query->get('montant'));
                $command->setDuration($request->query->getInt('abonnement', PayboxPaymentSubscription::NONE));
            }
        }

        $form = $this
            ->createForm(DonationRequestDonatorType::class, $command, [
                'from_adherent' => (bool) $command->getAdherentId(),
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->processor->doAcceptTermsAndConditions($command);

            return $this->redirectToRoute('app_renaissance_donation_mentions');
        }

        return $this->render('renaissance/donation/fill_personal_info.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

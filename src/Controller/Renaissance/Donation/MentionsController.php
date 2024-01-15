<?php

namespace App\Controller\Renaissance\Donation;

use App\Donation\Handler\DonationRequestHandler;
use App\Donation\Request\DonationRequestUtils;
use App\Form\Renaissance\Donation\DonationRequestMentionsType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v1/don/mentions', name: 'app_renaissance_donation_mentions', methods: ['GET|POST'])]
class MentionsController extends AbstractDonationController
{
    public function __invoke(
        Request $request,
        DonationRequestUtils $donationRequestUtils,
        DonationRequestHandler $donationRequestHandler
    ): Response {
        $command = $this->getCommand();
        $command->setRecaptcha($request->request->get('frc-captcha-solution'));

        if (!$this->processor->canAcceptTermsAndConditions($command)) {
            return $this->redirectToRoute('app_renaissance_donation_informations');
        }

        $this->processor->doAcceptTermsAndConditions($command);

        $form = $this
            ->createForm(DonationRequestMentionsType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $command->setClientIp($request->getClientIp());

            $donation = $donationRequestHandler->handle($command);

            return $this->redirectToRoute('app_renaissance_donation_payment', [
                'uuid' => $donation->getUuid(),
            ]);
        }

        return $this->render('renaissance/donation/mentions.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

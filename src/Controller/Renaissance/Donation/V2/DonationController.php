<?php

namespace App\Controller\Renaissance\Donation\V2;

use App\Donation\Handler\DonationRequestHandler;
use App\Donation\Request\DonationRequest;
use App\Entity\Adherent;
use App\Form\DonationRequestV2Type;
use App\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/v2/don', name: 'app_donation_index', methods: ['GET', 'POST'])]
class DonationController extends AbstractController
{
    private int $step = 0;

    public function __construct(
        private readonly DonationRequestHandler $donationRequestHandler,
    ) {
    }

    public function __invoke(Request $request, AnonymousFollowerSession $anonymousFollowerSession): Response
    {
        if ($response = $anonymousFollowerSession->start($request)) {
            return $response;
        }

        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $donationRequest = $this->getDonationRequest($request, $adherent);

        $form = $this
            ->createForm(DonationRequestV2Type::class, $donationRequest)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $donation = $this->donationRequestHandler->handle($donationRequest, $adherent);

            return $this->redirectToRoute('app_payment', ['uuid' => $donation->getUuid()]);
        }

        return $this->renderForm('renaissance/donation/form.html.twig', [
            'form' => $form,
            'step' => $this->step,
        ]);
    }

    private function getDonationRequest(Request $request, ?Adherent $currentUser): DonationRequest
    {
        $clientIp = $request->getClientIp();
        $defaultAmount = DonationRequest::DEFAULT_AMOUNT_V2;

        if ($currentUser) {
            return DonationRequest::createFromAdherent($currentUser, $clientIp, $defaultAmount);
        }

        return new DonationRequest($clientIp, $defaultAmount);
    }
}

<?php

declare(strict_types=1);

namespace App\Controller\Renaissance\Donation\V2;

use App\Donation\Handler\DonationRequestHandler;
use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Donation\Request\DonationRequest;
use App\Entity\Adherent;
use App\Form\DonationRequestV2Type;
use App\Security\Http\Session\AnonymousFollowerSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/don', name: 'app_donation_index', methods: ['GET', 'POST'])]
class DonationController extends AbstractController
{
    private const DEFAULT_STEP = 0;

    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly DonationRequestHandler $donationRequestHandler,
    ) {
    }

    public function __invoke(Request $request, AnonymousFollowerSession $anonymousFollowerSession): Response
    {
        if ($response = $anonymousFollowerSession->start($request)) {
            return $response;
        }

        /** @var Adherent|null $adherent */
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

        return $this->render('renaissance/donation/form.html.twig', [
            'form' => $form->createView(),
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
            'step' => $this->getCurrentStep($request, $adherent),
        ]);
    }

    private function getDonationRequest(Request $request, ?Adherent $currentUser): DonationRequest
    {
        $duration = $request->query->getInt('duration', PayboxPaymentSubscription::NONE);

        if (!PayboxPaymentSubscription::isValid($duration)) {
            $duration = PayboxPaymentSubscription::NONE;
        }

        $isSubscription = PayboxPaymentSubscription::NONE === $duration;

        $amount = max(
            min(
                $request->query->getInt(
                    'amount',
                    $isSubscription ? DonationRequest::DEFAULT_AMOUNT_V2 : DonationRequest::DEFAULT_AMOUNT_SUBSCRIPTION_V2
                ),
                $isSubscription ? DonationRequest::MAX_AMOUNT : DonationRequest::MAX_AMOUNT_SUBSCRIPTION
            ),
            $isSubscription ? DonationRequest::MIN_AMOUNT : DonationRequest::MIN_AMOUNT_SUBSCRIPTION
        );

        $localDestination = $request->query->getBoolean('localDestination');

        $donationRequest = DonationRequest::create($request, $amount, $duration, $currentUser);
        $donationRequest->localDestination = $localDestination;

        return $donationRequest;
    }

    private function getCurrentStep(Request $request, ?Adherent $adherent = null): int
    {
        if ($adherent && $request->query->has('step')) {
            return min(abs($request->query->getInt('step', self::DEFAULT_STEP)), 1);
        }

        return self::DEFAULT_STEP;
    }
}

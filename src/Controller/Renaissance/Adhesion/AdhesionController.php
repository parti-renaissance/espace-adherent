<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Adhesion\Request\MembershipRequest;
use App\Controller\Renaissance\Adhesion\Api\PersistEmailController;
use App\Donation\Handler\DonationRequestHandler;
use App\Donation\Paybox\PayboxPaymentSubscription;
use App\Donation\Request\DonationRequest;
use App\Entity\Adherent;
use App\Entity\Referral;
use App\Form\MembershipRequestType;
use App\Security\Http\Session\AnonymousFollowerSession;
use App\Utils\UtmParams;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/adhesion', name: self::ROUTE_NAME, methods: ['GET', 'POST'])]
#[Route('/adhesion/{pid}', name: 'app_adhesion_with_pid', requirements: ['pid' => '%pattern_pid%'], methods: ['GET', 'POST'])]
#[Route('/adhesion/{identifier}', name: 'app_adhesion_with_invitation', requirements: ['identifier' => 'P[A-Z0-9]{5}'], methods: ['GET', 'POST'])]
class AdhesionController extends AbstractController
{
    public const ROUTE_NAME = 'app_adhesion_index';

    private int $step = 0;

    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly DonationRequestHandler $donationRequestHandler,
        private readonly AnonymousFollowerSession $anonymousFollowerSession,
    ) {
    }

    public function __invoke(Request $request, ?string $pid = null, ?Referral $referral = null): Response
    {
        if ($response = $this->anonymousFollowerSession->start($request)) {
            return $response;
        }

        if (($currentUser = $this->getUser()) instanceof Adherent && $currentUser->hasActiveMembership()) {
            return $this->redirectToRoute('vox_app_profile');
        }

        $membershipRequest = $this->getMembershipRequest($request, $currentUser, $referral);

        $form = $this
            ->createForm(MembershipRequestType::class, $membershipRequest, [
                'from_certified_adherent' => $currentUser && $currentUser->isCertified(),
                'validation_groups' => ['adhesion:amount'],
                'csrf_protection' => false,
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $donationRequest = DonationRequest::create($request, $membershipRequest->amount, PayboxPaymentSubscription::NONE, $currentUser);
            $donationRequest->forMembership();

            $donation = $this->donationRequestHandler->handle($donationRequest, $currentUser, (bool) $currentUser?->isRenaissanceAdherent());

            return $this->redirectToRoute(
                'app_payment',
                UtmParams::mergeParams(['uuid' => $donation->getUuid()], $donation->utmSource, $donation->utmCampaign)
            );
        }

        return $this->render('renaissance/adhesion/form.html.twig', [
            'form' => $form->createView(),
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
            'step' => $this->step,
            'pid' => $pid,
            'referral' => $referral,
        ]);
    }

    private function getMembershipRequest(Request $request, ?Adherent $currentUser, ?Referral $referral): MembershipRequest
    {
        if ($currentUser) {
            // Create membership from connected user (like a sympathizer or an adherent who wants to renew)
            $membershipRequest = MembershipRequest::createFromAdherent($currentUser);

            $this->step = $referral ? 3 : 1;
        } else {
            // Create empty membership request otherwise
            $membershipRequest = new MembershipRequest();
            $membershipRequest->email = $request->query->get('email');

            if ($referral) {
                $membershipRequest->email = $referral->emailAddress;
                $this->step = 1;
            } elseif ($emailIdentifier = $request->getSession()->get(PersistEmailController::SESSION_KEY)) {
                $membershipRequest->email = $emailIdentifier;
                $this->step = 1;
            }
        }

        if ($request->query->has(UtmParams::UTM_SOURCE)) {
            $membershipRequest->utmSource = UtmParams::filterUtmParameter($request->query->get(UtmParams::UTM_SOURCE));
        }
        if ($request->query->has(UtmParams::UTM_CAMPAIGN)) {
            $membershipRequest->utmCampaign = UtmParams::filterUtmParameter($request->query->get(UtmParams::UTM_CAMPAIGN));
        }

        return $membershipRequest;
    }
}

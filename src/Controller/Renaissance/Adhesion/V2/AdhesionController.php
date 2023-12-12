<?php

namespace App\Controller\Renaissance\Adhesion\V2;

use App\Adhesion\MembershipRequest;
use App\Controller\CanaryControllerTrait;
use App\Controller\Renaissance\Adhesion\V2\Api\CreateAccountController;
use App\Controller\Renaissance\Adhesion\V2\Api\PersistEmailController;
use App\Donation\Handler\DonationRequestHandler;
use App\Donation\Request\DonationRequest;
use App\Entity\Adherent;
use App\Form\MembershipRequestType;
use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use App\Repository\AdherentRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/v2/adhesion', name: 'app_adhesion_index', methods: ['GET', 'POST'])]
class AdhesionController extends AbstractController
{
    use CanaryControllerTrait;

    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly DonationRequestHandler $donationRequestHandler,
        private readonly AdherentRepository $adherentRepository,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        $this->disableInProduction();

        if (($currentUser = $this->getUser()) instanceof Adherent && $currentUser->hasActiveMembership()) {
            return $this->redirectToRoute('app_renaissance_adherent_space');
        }

        /** @var MembershipRequest $membershipRequest */
        [$membershipRequest, $adherent] = $this->getMembershipRequest($request, $currentUser);

        if ($request->query->has(RenaissanceMembershipRequest::UTM_SOURCE)) {
            $membershipRequest->utmSource = $this->filterUtmParameter((string) $request->query->get(RenaissanceMembershipRequest::UTM_SOURCE));
            $membershipRequest->utmCampaign = $this->filterUtmParameter((string) $request->query->get(RenaissanceMembershipRequest::UTM_CAMPAIGN));
        }

        $form = $this
            ->createForm(MembershipRequestType::class, $membershipRequest, ['validation_groups' => ['adhesion:amount']])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $donationRequest = DonationRequest::createFromAdherent($adherent, $request->getClientIp(), $membershipRequest->amount);
            $donationRequest->forMembership();

            $donation = $this->donationRequestHandler->handle($donationRequest, $adherent, $adherent->isRenaissanceAdherent());

            return $this->redirectToRoute('app_adhesion_payment', ['uuid' => $donation->getUuid()]);
        }

        return $this->renderForm('renaissance/adhesion/form.html.twig', [
            'form' => $form,
            'email_validation_token' => $this->csrfTokenManager->getToken('email_validation_token'),
        ]);
    }

    private function getMembershipRequest(Request $request, ?Adherent $currentUser): array
    {
        if ($currentUser) {
            // Create membership from connected user (like a sympathizer or an adherent who wants to renew)
            $membershipRequest = MembershipRequest::createFromAdherent($currentUser);
        } elseif (
            ($accountIdentifier = $request->getSession()->get(CreateAccountController::SESSION_KEY))
            && ($currentUser = $this->adherentRepository->findOneByValidUuid($accountIdentifier))
        ) {
            // Create membership from freshly created account (take account_identifier from session)
            $membershipRequest = MembershipRequest::createFromAdherent($currentUser);
        } else {
            // Create empty membership request otherwise
            $membershipRequest = new MembershipRequest();

            if ($emailIdentifier = $request->getSession()->get(PersistEmailController::SESSION_KEY)) {
                $membershipRequest->email = $emailIdentifier;
            } else {
                $membershipRequest->email = $request->query->get('email');
            }
        }

        if ($request->query->has(RenaissanceMembershipRequest::UTM_SOURCE)) {
            $membershipRequest->utmSource = $this->filterUtmParameter((string) $request->query->get(RenaissanceMembershipRequest::UTM_SOURCE));
            $membershipRequest->utmCampaign = $this->filterUtmParameter((string) $request->query->get(RenaissanceMembershipRequest::UTM_CAMPAIGN));
        }

        return [$membershipRequest, $currentUser];
    }

    private function filterUtmParameter($utmParameter): ?string
    {
        if (!$utmParameter) {
            return null;
        }

        return mb_substr($utmParameter, 0, 255);
    }
}

<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Donation\DonationRequest;
use App\Donation\DonationRequestHandler;
use App\Form\Renaissance\Adhesion\MembershipRequestProceedPaymentType;
use App\Membership\MembershipRequest\RenaissanceMembershipRequest;
use App\Membership\MembershipRequestHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/recapitulatif", name="app_renaissance_adhesion_summary", methods={"GET|POST"})
 */
class SummaryController extends AbstractAdhesionController
{
    public function __invoke(
        Request $request,
        MembershipRequestHandler $membershipRequestHandler,
        DonationRequestHandler $donationRequestHandler
    ): Response {
        $membershipRequestCommand = $this->storage->getMembershipRequestCommand();

        if (!$this->processor->canValidSummary($membershipRequestCommand)) {
            return $this->redirectToRoute('app_renaissance_adhesion_mentions');
        }

        $form = $this
            ->createForm(MembershipRequestProceedPaymentType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->processor->canPayMembership($membershipRequestCommand)) {
                return $this->redirectToRoute('app_renaissance_adhesion_summary');
            }

            $this->processor->doPayMembership($membershipRequestCommand);

            $adherent = $membershipRequestHandler
                ->createRenaissanceAdherent(
                    RenaissanceMembershipRequest::createFromCommand($membershipRequestCommand)
                )
            ;

            $donationRequest = DonationRequest::createFromAdherent($adherent, $membershipRequestCommand->getClientIp(), $membershipRequestCommand->getAmount());
            $donationRequest->forMembership();

            $donationRequestHandler->handle($donationRequest);

            return $this->redirectToRoute('app_renaissance_adhesion_payment', [
                'uuid' => $donationRequest->getUuid(),
            ]);
        }

        return $this->render('renaissance/adhesion/summary.html.twig', [
            'form' => $form->createView(),
            'membershipRequest' => $membershipRequestCommand,
        ]);
    }
}

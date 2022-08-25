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
 * @Route(path="/adhesion/recapitulatif", name="app_renaissance_adhesion_summary", methods={"GET|POST"})
 */
class SummaryController extends AbstractAdhesionController
{
    public function __invoke(
        Request $request,
        MembershipRequestHandler $membershipRequestHandler,
        DonationRequestHandler $donationRequestHandler
    ): Response {
        $command = $this->getCommand();

        if (!$this->processor->canValidSummary($command)) {
            return $this->redirectToRoute('app_renaissance_adhesion_mentions');
        }

        $this->processor->doValidSummary($command);

        $form = $this
            ->createForm(MembershipRequestProceedPaymentType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $command->setClientIp($request->getClientIp());

            $adherent = $membershipRequestHandler->createRenaissanceAdherent(
                RenaissanceMembershipRequest::createFromCommand($command)
            );

            $donationRequest = DonationRequest::createFromAdherent($adherent, $command->getClientIp(), $command->getAmount());
            $donationRequest->forMembership();

            $donationRequestHandler->handle($donationRequest);

            return $this->redirectToRoute('app_renaissance_adhesion_payment', [
                'uuid' => $donationRequest->getUuid(),
            ]);
        }

        return $this->render('renaissance/adhesion/summary.html.twig', [
            'form' => $form->createView(),
            'command' => $command,
        ]);
    }
}

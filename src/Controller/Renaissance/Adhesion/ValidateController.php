<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Donation\DonationRequest;
use App\Donation\DonationRequestHandler;
use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Form\Renaissance\Adhesion\AdhesionPaymentType;
use App\Membership\MembershipRequestHandler;
use App\Repository\AdherentRepository;
use App\Security\AuthenticationUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     path="/adhesion/finaliser/{uuid}/{token}",
 *     name="app_renaissance_membership_validate",
 *     requirements={
 *         "uuid": "%pattern_uuid%",
 *         "token": "%pattern_uuid%"
 *     },
 *     methods={"GET|POST"}
 * )
 *
 * @Entity("adherentRequest", expr="repository.findOneBy({'uuid': uuid, 'token': token})")
 */
class ValidateController extends AbstractController
{
    public function __invoke(
        Request $request,
        AdherentRequest $adherentRequest,
        AdherentRepository $adherentRepository,
        MembershipRequestHandler $membershipRequestHandler,
        DonationRequestHandler $donationRequestHandler,
        AuthenticationUtils $authenticationUtils
    ): Response {
        $form = $this
            ->createForm(AdhesionPaymentType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid() && $adherentRequest->getAdherent()) {
            $donationRequest = DonationRequest::createFromAdherent(
                $adherentRequest->getAdherent(),
                $request->getClientIp(),
                $adherentRequest->amount
            );
            $donationRequest->forMembership();

            $donation = $donationRequestHandler->handle($donationRequest);

            return $this->redirectToRoute('app_renaissance_adhesion_payment', [
                'uuid' => $donation->getUuid(),
            ]);
        }

        // Todo we need to handle adherent ActivateAt here or when the process is finish?
        if ($adherent = $adherentRepository->findOneByEmail($adherentRequest->email)) {
            if ($adherent->getLastMembershipDonation()) {
                $authenticationUtils->authenticateAdherent($adherent);

                return $this->redirectToRoute('app_renaissance_adhesion_additional_informations');
            }

            $membershipRequestHandler->createOrUpdateRenaissanceAdherent($adherentRequest, $adherent);
        } else {
            $membershipRequestHandler->createOrUpdateRenaissanceAdherent($adherentRequest);
        }

        return $this->render('renaissance/adhesion/validate.html.twig', [
            'adherent_request' => $adherentRequest,
            'form' => $form->createView(),
        ]);
    }
}

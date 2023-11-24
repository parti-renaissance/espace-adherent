<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Entity\Renaissance\Adhesion\AdherentRequest;
use App\Membership\MembershipRequestHandler;
use App\Repository\AdherentRepository;
use App\Security\AuthenticationUtils;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/adhesion/finaliser/{uuid}/{token}', name: 'app_renaissance_membership_validate', requirements: ['uuid' => '%pattern_uuid%', 'token' => '%pattern_uuid%'], methods: ['GET'])]
#[Entity('adherentRequest', expr: "repository.findOneBy({'uuid': uuid, 'token': token})")]
class ValidateController extends AbstractController
{
    public function __invoke(
        LoggerInterface $logger,
        Request $request,
        AdherentRequest $adherentRequest,
        AdherentRepository $adherentRepository,
        MembershipRequestHandler $membershipRequestHandler,
        AuthenticationUtils $authenticationUtils
    ): Response {
        // Step 1 : create or update existing adherent
        $adherent = $adherentRepository->findOneByEmail($adherentRequest->email);
        $tokenUsedAt = $adherentRequest->tokenUsedAt;

        if (null === $tokenUsedAt || null === $adherent) {
            $adherent = $membershipRequestHandler->createOrUpdateRenaissanceAdherent($adherentRequest, $adherent);
        }

        if (!$adherent) {
            $logger->error('[Validation compte] adherent introuvable, adherentRequest : '.$adherentRequest->getId());

            $this->addFlash('error', 'Une erreur s\'est produite.');

            return $this->redirectToRoute('app_renaissance_adhesion');
        }

        // Step 2 : connect existing adherent
        $authenticationUtils->authenticateAdherent($adherent);

        // Step 3 : redirect to final step if already paid
        if ($adherent->getLastMembershipDonation()) {
            return $this->redirectToRoute('app_renaissance_adhesion_additional_informations');
        }

        $request->getSession()->set(PaymentController::AMOUNT_SESSION_KEY, $adherentRequest->amount);

        return $this->redirectToRoute('app_renaissance_adhesion_complete_profile');
    }
}

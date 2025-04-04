<?php

namespace App\Controller\Renaissance\Referral;

use App\Adhesion\Command\CreateAccountCommand;
use App\Adhesion\CreateAdherentResult;
use App\Adhesion\Request\MembershipRequest;
use App\Entity\Referral;
use App\Form\MembershipFromReferralType;
use App\Security\AdherentLogin;
use App\Utils\UtmParams;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invitation/{identifier}', name: self::ROUTE_NAME, requirements: ['identifier' => 'P[A-Z0-9]{5}'], methods: ['GET', 'POST'])]
class AdhesionController extends AbstractController
{
    use HandleTrait;

    public const ROUTE_NAME = 'app_referral_adhesion';

    public function __construct(
        private readonly AdherentLogin $adherentLogin,
        MessageBusInterface $messageBus,
    ) {
        $this->messageBus = $messageBus;
    }

    public function __invoke(Request $request, Referral $referral): Response
    {
        if (!$referral->isAdhesion() || !$referral->isInProgress()) {
            return $this->redirectToRoute('app_adhesion_index', $request->query->all());
        }

        if ($referral->isInvitation()) {
            return $this->redirectToRoute('app_adhesion_with_invitation', array_merge($request->query->all(), ['identifier' => $referral->identifier]));
        }

        $membershipRequest = MembershipRequest::createFromReferral($referral);
        if ($request->query->has(UtmParams::UTM_SOURCE)) {
            $membershipRequest->utmSource = UtmParams::filterUtmParameter($request->query->get(UtmParams::UTM_SOURCE));
        }
        if ($request->query->has(UtmParams::UTM_CAMPAIGN)) {
            $membershipRequest->utmCampaign = UtmParams::filterUtmParameter($request->query->get(UtmParams::UTM_CAMPAIGN));
        }

        $form = $this
            ->createForm(MembershipFromReferralType::class, $membershipRequest)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $isValid = $form->isValid()) {
            $result = $this->handle(new CreateAccountCommand($membershipRequest));

            if ($result instanceof CreateAdherentResult && $result->getAdherent()) {
                $this->adherentLogin->login($result->getAdherent());

                return $this->redirectToRoute('app_adhesion_with_invitation', array_merge($request->query->all(), ['identifier' => $referral->identifier]));
            }

            $this->addFlash('error', 'Une erreur est survenue lors de la création de votre compte. Veuillez réessayer plus tard.');

            return $this->redirectToRoute(self::ROUTE_NAME, array_merge($request->query->all(), ['identifier' => $referral->identifier]));
        }

        return $this->render('renaissance/referral/adhesion.html.twig', [
            'referral' => $referral,
            'form' => $form->createView(),
            'is_valid' => $isValid ?? null,
        ]);
    }
}

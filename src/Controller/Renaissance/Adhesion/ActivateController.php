<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Entity\Adherent;
use App\Entity\AdherentActivationToken;
use App\Exception\AdherentAlreadyEnabledException;
use App\Exception\AdherentTokenExpiredException;
use App\Membership\AdherentAccountActivationHandler;
use App\Membership\MembershipNotifier;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * This action enables a new user to activate his\her newly created
 * membership account.
 *
 * @Route(
 *     path="/adhesion/finaliser/{adherent_uuid}/{activation_token}",
 *     name="app_renaissance_membership_activate",
 *     requirements={
 *         "adherent_uuid": "%pattern_uuid%",
 *         "activation_token": "%pattern_sha1%"
 *     },
 *     methods={"GET"}
 * )
 * @Entity("adherent", expr="repository.findOneByUuid(adherent_uuid)")
 * @Entity("activationToken", expr="repository.findByToken(activation_token)")
 */
class ActivateController extends AbstractController
{
    public function __invoke(
        Adherent $adherent,
        AdherentActivationToken $activationToken,
        AdherentAccountActivationHandler $accountActivationHandler,
        MembershipNotifier $membershipNotifier
    ): Response {
        try {
            $accountActivationHandler->handle($adherent, $activationToken);

            if ($adherent->isAdherent()) {
                $membershipNotifier->sendConfirmationJoinMessage($adherent);
            }

            return $this->render('renaissance/adhesion/adhesion_complete.html.twig');
        } catch (AdherentAlreadyEnabledException $e) {
            $this->addFlash('info', 'adherent.activation.already_active');
        } catch (AdherentTokenExpiredException $e) {
            $this->addFlash('info', 'adherent.activation.expired_key');
        }

        return $this->redirectToRoute('app_ren_homepage');
    }
}

<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Entity\Adherent;
use App\Form\Renaissance\Adhesion\CompleteProfilType;
use App\Membership\MembershipRegistrationProcess;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *     path="/adhesion/completer-mon-profil",
 *     name="app_renaissance_adhesion_complete_profil",
 *     methods={"GET|POST"}
 * )
 *
 * @Security("is_granted('ROLE_ADHERENT')")
 */
class CompleteProfilController extends AbstractController
{
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        MembershipRegistrationProcess $membershipRegistrationProcess
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $form = $this
            ->createForm(CompleteProfilType::class, $adherent, ['from_certified_adherent' => $adherent->isCertified()])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_renaissance_adhesion_pre_payment', ['amount' => $request->getSession()->get(PaymentController::AMOUNT_SESSION_KEY)]);
        }

        return $this->render('renaissance/adhesion/complete_profil.html.twig', [
           'form' => $form->createView(),
        ]);
    }
}

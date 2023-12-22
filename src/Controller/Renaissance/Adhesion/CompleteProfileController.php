<?php

namespace App\Controller\Renaissance\Adhesion;

use App\Entity\Adherent;
use App\Form\Renaissance\Adhesion\CompleteProfileType;
use App\Membership\MembershipRegistrationProcess;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/v1/adhesion/completer-mon-profil', name: 'app_renaissance_adhesion_complete_profile', methods: ['GET|POST'])]
#[IsGranted('ROLE_ADHERENT')]
class CompleteProfileController extends AbstractController
{
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        MembershipRegistrationProcess $membershipRegistrationProcess
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();

        $form = $this
            ->createForm(CompleteProfileType::class, $adherent, ['from_certified_adherent' => $adherent->isCertified()])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_renaissance_adhesion_pre_payment', ['amount' => $request->getSession()->get(PaymentController::AMOUNT_SESSION_KEY)]);
        }

        return $this->render('renaissance/adhesion/complete_profile.html.twig', [
           'form' => $form->createView(),
        ]);
    }
}

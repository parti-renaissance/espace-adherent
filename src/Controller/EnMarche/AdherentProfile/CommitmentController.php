<?php

namespace App\Controller\EnMarche\AdherentProfile;

use App\Controller\EnMarche\VotingPlatform\AbstractController;
use App\Entity\AdherentCommitment;
use App\Form\AdherentCommitmentType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class CommitmentController extends AbstractController
{
    /**
     * @Route("/parametres/engagement", name="app_adherent_commitment", methods={"GET", "POST"})
     */
    public function indexAction(
        Request $request,
        UserInterface $adherent,
        EntityManagerInterface $entityManager
    ): Response {
        $commitment = $adherent->getCommitment() ?? new AdherentCommitment($adherent);

        $form = $this->createForm(AdherentCommitmentType::class, $commitment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($form->getData());
            $entityManager->flush();

            $this->addFlash('info', 'adherent_commitment.update_success');

            return $this->redirectToRoute('app_adherent_commitment');
        }

        return $this->render('adherent_profile/commitment.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

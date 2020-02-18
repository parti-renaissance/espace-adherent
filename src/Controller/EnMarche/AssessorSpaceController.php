<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Election\ElectionManager;
use AppBundle\Entity\Adherent;
use AppBundle\Form\VoteResultType;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @Route("/espace-assesseur", name="app_assessor_space")
 *
 * @Security("is_granted('ROLE_ASSESSOR')")
 */
class AssessorSpaceController extends AbstractController
{
    /**
     * @Route("/resultats", name="_vote_results", methods={"GET", "POST"})
     */
    public function submitVoteResultsAction(
        Request $request,
        ElectionManager $electionManager,
        EntityManagerInterface $entityManager,
        UserInterface $adherent
    ): Response {
        /** @var Adherent $adherent */
        $voteResult = $electionManager->getVoteResultForCurrentElectionRound(
            $adherent->getAssessorRole()->getVotePlace(),
            $adherent
        );

        $form = $this
            ->createForm(VoteResultType::class, $voteResult)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$voteResult->getId()) {
                $entityManager->persist($voteResult);
            }

            $entityManager->flush();

            $this->addFlash('info', 'Résultats ont bien été sauvegardés');

            return $this->redirectToRoute('app_assessor_space_vote_results');
        }

        return $this->render('assessor_space/vote_results.html.twig', [
            'form' => $form->createView(),
            'vote_result' => $voteResult,
        ]);
    }
}

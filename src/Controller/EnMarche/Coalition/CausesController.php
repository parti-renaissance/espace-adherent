<?php

namespace App\Controller\EnMarche\Coalition;

use App\Coalition\Filter\CauseFilter;
use App\Coalition\MessageNotifier;
use App\Entity\Coalition\Cause;
use App\Form\Coalition\CauseFilterType;
use App\Repository\Coalition\CauseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(path="/espace-coalition/causes", name="app_coalition_", methods={"GET"})
 *
 * @Security("is_granted('ROLE_COALITION_MODERATOR')")
 */
class CausesController extends AbstractController
{
    /**
     * @Route("", name="causes_list", methods={"GET"})
     */
    public function list(Request $request, CauseRepository $causeRepository): Response
    {
        $filter = new CauseFilter(Cause::STATUS_PENDING);

        $form = $this->createForm(CauseFilterType::class, $filter)->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = new CauseFilter(Cause::STATUS_PENDING);
        }

        return $this->render('coalition/causes_list.html.twig', [
            'causes' => $causeRepository->searchByFilter($filter, $request->query->getInt('page', 1)),
            'total_count' => $causeRepository->countCauses(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{slug}/approuver", name="approve_cause", methods={"POST"})
     */
    public function approveCause(
        Cause $cause,
        EntityManagerInterface $entityManager,
        MessageNotifier $notifier
    ): Response {
        $cause->approve();

        $entityManager->flush($cause);

        $notifier->sendCauseApprovalMessage($cause);

        return $this->redirectToRoute('app_coalition_causes_list');
    }
}

<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Committee\CommitteeMergeCommandHandler;
use AppBundle\Entity\Reporting\CommitteeMergeHistory;
use AppBundle\Form\ConfirmActionType;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminCommitteeMergeHistoryController extends CRUDController
{
    public function revertAction(Request $request, CommitteeMergeCommandHandler $committeeMergeCommandHandler): Response
    {
        /** @var CommitteeMergeHistory $committeeMergeHistory */
        $committeeMergeHistory = $this->admin->getSubject();

        $this->admin->checkAccess('revert', $committeeMergeHistory);

        if ($committeeMergeHistory->isReverted()) {
            $this->addFlash('error', 'Cette fusion de comités a déjà été annulée.');

            return $this->redirectToList();
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $committeeMergeCommandHandler->revert($committeeMergeHistory, $this->getUser());

                $this->addFlash('success', 'La fusion de comités a bien été annulée.');
            }

            return $this->redirectToList();
        }

        return $this->renderWithExtraParams('admin/committee/merge/revert.html.twig', [
            'form' => $form->createView(),
            'object' => $committeeMergeHistory,
            'action' => 'revert',
            'elements' => $this->admin->getShow(),
        ]);
    }
}

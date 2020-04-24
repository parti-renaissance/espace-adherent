<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Committee\CommitteeMergeCommand;
use AppBundle\Committee\CommitteeMergeCommandHandler;
use AppBundle\Entity\Reporting\CommitteeMergeHistory;
use AppBundle\Form\Admin\CommitteeMergeType;
use AppBundle\Form\ConfirmActionType;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminCommitteeMergeHistoryController extends CRUDController
{
    public function mergeAction(Request $request, CommitteeMergeCommandHandler $committeeMergeHandler): Response
    {
        $this->admin->checkAccess('merge');

        $committeeMergeCommand = new CommitteeMergeCommand($this->getUser());

        $form = $this
            ->createForm(CommitteeMergeType::class, $committeeMergeCommand)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $committeeMergeHandler->handle($committeeMergeCommand);

                $this->addFlash('success', 'Fusion effectuée avec succès!');

                return $this->redirectToList();
            }

            return $this->renderWithExtraParams('admin/committee/merge/confirm.html.twig', [
                'form' => $form->createView(),
                'source_committee' => $committeeMergeCommand->getSourceCommittee(),
                'destination_committee' => $committeeMergeCommand->getDestinationCommittee(),
                'new_members_count' => $committeeMergeHandler->countNewMembers($committeeMergeCommand),
                'action' => 'merge',
                'elements' => $this->admin->getShow(),
            ]);
        }

        return $this->renderWithExtraParams('admin/committee/merge/request.html.twig', [
            'form' => $form->createView(),
            'action' => 'merge',
            'elements' => $this->admin->getShow(),
        ]);
    }

    public function revertAction(Request $request, CommitteeMergeCommandHandler $committeeMergeHandler): Response
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
                $committeeMergeHandler->revert($committeeMergeHistory, $this->getUser());

                $this->addFlash('success', 'La fusion de comités a bien été annulée.<br>Veuillez nommer un animateur.');
            }

            return $this->redirectToRoute('app_admin_committee_members', [
                'id' => $committeeMergeHistory->getSourceCommittee()->getId(),
            ]);
        }

        return $this->renderWithExtraParams('admin/committee/merge/revert.html.twig', [
            'form' => $form->createView(),
            'object' => $committeeMergeHistory,
            'action' => 'revert',
            'elements' => $this->admin->getShow(),
        ]);
    }
}

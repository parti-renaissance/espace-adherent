<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Committee\CommitteeMergeCommand;
use AppBundle\Committee\CommitteeMergeCommandHandler;
use AppBundle\Committee\MultipleReferentsFoundException;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Exception\BaseGroupException;
use AppBundle\Exception\CommitteeMembershipException;
use AppBundle\Form\Admin\CommitteeMergeType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/committee")
 */
class AdminCommitteeController extends Controller
{
    /**
     * Approves the committee.
     *
     * @Route("/{id}/approve", name="app_admin_committee_approve", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_COMMITTEES')")
     */
    public function approveAction(Committee $committee): Response
    {
        try {
            $this->get('app.committee.authority')->approve($committee);
            $this->addFlash('sonata_flash_success', sprintf('Le comité « %s » a été approuvé avec succès.', $committee->getName()));
        } catch (BaseGroupException $exception) {
            throw $this->createNotFoundException(sprintf('Committee %u must be pending in order to be approved.', $committee->getId()), $exception);
        }

        try {
            $this->get('app.committee.authority')->notifyReferentsForApproval($committee);
        } catch (MultipleReferentsFoundException $exception) {
            $this->addFlash('warning', sprintf(
                'Attention, plusieurs référents (%s) ont été trouvés dans le département de ce nouveau comité. 
                Aucun mail de notification pour la validation de ce comité ne leur a été envoyé. 
                Nommez un seul référent pour permettre les notifications de ce type.',
                implode(', ', array_map(function (Adherent $referent) {
                    return $referent->getEmailAddress();
                }, $exception->getReferents()->toArray()))
            ));
        }

        return $this->redirectToRoute('admin_app_committee_list');
    }

    /**
     * Refuses the committee.
     *
     * @Route("/{id}/refuse", name="app_admin_committee_refuse", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_COMMITTEES')")
     */
    public function refuseAction(Committee $committee): Response
    {
        try {
            $this->get('app.committee.authority')->refuse($committee);
            $this->addFlash('sonata_flash_success', sprintf('Le comité « %s » a été refusé avec succès.', $committee->getName()));
        } catch (BaseGroupException $exception) {
            throw $this->createNotFoundException(sprintf('Committee %u must be pending in order to be refused.', $committee->getId()), $exception);
        }

        return $this->redirectToRoute('admin_app_committee_list');
    }

    /**
     * @Route("/{id}/members", name="app_admin_committee_members", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_COMMITTEES')")
     */
    public function membersAction(Committee $committee): Response
    {
        $manager = $this->get('app.committee.manager');

        return $this->render('admin/committee/members.html.twig', [
            'committee' => $committee,
            'memberships' => $memberships = $manager->getCommitteeMemberships($committee),
            'supervisors_count' => $memberships->countCommitteeSupervisorMemberships(),
        ]);
    }

    /**
     * @Route("/{committee}/members/{adherent}/set-privilege/{privilege}", name="app_admin_committee_change_privilege", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_COMMITTEES')")
     */
    public function changePrivilegeAction(
        Request $request,
        Committee $committee,
        Adherent $adherent,
        string $privilege
    ): Response {
        if (!$committee->isApproved()) {
            throw new BadRequestHttpException('Committee must be approved to change member privileges.');
        }

        if (!$this->isCsrfTokenValid(sprintf('committee.change_privilege.%s', $adherent->getId()), $request->query->get('token'))) {
            throw new BadRequestHttpException('Invalid Csrf token provided.');
        }

        try {
            $this->get('app.committee.manager')->changePrivilege($adherent, $committee, $privilege);
        } catch (CommitteeMembershipException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_committee_members', [
            'id' => $committee->getId(),
        ]);
    }

    /**
     * @Route("/merge", name="app_admin_committee_merge", methods={"GET", "POST"})
     *
     * @Security("has_role('ROLE_ADMIN_COMMITTEES_MERGE')")
     */
    public function mergeAction(Request $request): Response
    {
        $committeeMergeCommand = new CommitteeMergeCommand($this->getUser());

        $form = $this
            ->createForm(CommitteeMergeType::class, $committeeMergeCommand)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $committeeMergeHandler = $this->get(CommitteeMergeCommandHandler::class);

            if ($form->get('confirm')->isClicked()) {
                $committeeMergeHandler->handle($committeeMergeCommand);

                $this->addFlash('success', 'Fusion effectuée avec succès!');

                return $this->redirectToRoute('admin_app_reporting_committeemergehistory_list');
            }

            return $this->render('admin/committee/merge/confirm.html.twig', [
                'form' => $form->createView(),
                'source_committee' => $committeeMergeCommand->getSourceCommittee(),
                'destination_committee' => $committeeMergeCommand->getDestinationCommittee(),
                'new_members_count' => $committeeMergeHandler->countNewMembers($committeeMergeCommand),
            ]);
        }

        return $this->render('admin/committee/merge/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

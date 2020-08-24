<?php

namespace App\Controller\Admin;

use App\Committee\CommitteeAdherentMandateManager;
use App\Committee\Exception\CommitteeAdherentMandateException;
use App\Committee\MultipleReferentsFoundException;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Exception\BaseGroupException;
use App\Exception\CommitteeMembershipException;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/committee")
 */
class AdminCommitteeController extends Controller
{
    private $mandateManager;
    private $mandateRepository;
    private $translator;

    public function __construct(
        CommitteeAdherentMandateManager $mandateManager,
        CommitteeAdherentMandateRepository $mandateRepository,
        TranslatorInterface $translator
    ) {
        $this->mandateManager = $mandateManager;
        $this->mandateRepository = $mandateRepository;
        $this->translator = $translator;
    }

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
            'active_mandates_adherent_ids' => $this->mandateRepository->findActiveMandateAdherentIds($committee),
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
     * @Route("/{committee}/members/{adherent}/{action}-mandate", name="app_admin_committee_change_mandate", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_COMMITTEES')")
     */
    public function changeMandateAction(
        Request $request,
        Committee $committee,
        Adherent $adherent,
        string $action
    ): Response {
        if (!\in_array($action, CommitteeAdherentMandateManager::ACTIONS)) {
            throw new BadRequestHttpException(\sprintf('Action "%s" is not authorized.', $action));
        }

        if (!$committee->isApproved()) {
            throw new BadRequestHttpException($this->translator->trans('adherent_mandate.committee.committee_not_approved'));
        }

        if (!$this->isCsrfTokenValid(\sprintf('committee.change_mandate.%s', $adherent->getId()), $request->query->get('token'))) {
            throw new BadRequestHttpException('Invalid Csrf token provided.');
        }

        try {
            if (CommitteeAdherentMandateManager::CREATE_ACTION === $action) {
                $this->mandateManager->createMandate($adherent, $committee);
            } elseif (CommitteeAdherentMandateManager::FINISH_ACTION === $action) {
                $this->mandateManager->endMandate($adherent, $committee);
            }
        } catch (CommitteeAdherentMandateException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_committee_members', [
            'id' => $committee->getId(),
        ]);
    }
}

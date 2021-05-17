<?php

namespace App\Controller\Admin;

use App\Committee\CommitteeAdherentMandateCommand;
use App\Committee\CommitteeAdherentMandateManager;
use App\Committee\CommitteeManagementAuthority;
use App\Committee\CommitteeManager;
use App\Committee\Exception\CommitteeAdherentMandateException;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\AbstractAdherentMandate;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Exception\BaseGroupException;
use App\Exception\CommitteeMembershipException;
use App\Form\Admin\CommitteeMandateCommandType;
use App\Form\ConfirmActionType;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/committee")
 */
class AdminCommitteeController extends AbstractController
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
     * Refuses the committee.
     *
     * @Route("/{id}/refuse", name="app_admin_committee_refuse", methods={"GET|POST"})
     * @Security("has_role('ROLE_ADMIN_COMMITTEES')")
     */
    public function refuseAction(
        Request $request,
        Committee $committee,
        CommitteeManagementAuthority $committeeManagementAuthority
    ): Response {
        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                try {
                    $committeeManagementAuthority->refuse($committee);
                    $this->addFlash('sonata_flash_success', sprintf('Le comité « %s » a été refusé avec succès.', $committee->getName()));
                } catch (BaseGroupException $exception) {
                    throw $this->createNotFoundException(sprintf('Committee %u must be pending in order to be refused.', $committee->getId()), $exception);
                }
            }

            return $this->redirectToRoute('admin_app_committee_list');
        }

        return $this->render('admin/committee/refuse.html.twig', [
            'form' => $form->createView(),
            'object' => $committee,
        ]);
    }

    /**
     * @Route("/{id}/members", name="app_admin_committee_members", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_COMMITTEES')")
     */
    public function membersAction(CommitteeManager $manager, Committee $committee): Response
    {
        return $this->render('admin/committee/members.html.twig', [
            'committee' => $committee,
            'memberships' => $memberships = $manager->getCommitteeMemberships($committee),
            'supervisors_count' => $memberships->countCommitteeSupervisorMemberships(),
            'active_mandates_adherent_ids' => $this->mandateRepository->findActiveMandateAdherentIds($committee),
        ]);
    }

    /**
     * @Route("/{id}/mandates", name="app_admin_committee_mandates", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_COMMITTEES')")
     */
    public function mandatesAction(Committee $committee): Response
    {
        return $this->render('admin/committee/mandates/list.html.twig', [
            'committee' => $committee,
        ]);
    }

    /**
     * @Route("/{id}/mandates/add", name="app_admin_committee_add_mandate", methods={"GET|POST"})
     * @Security("has_role('ROLE_ADMIN_COMMITTEES') and is_granted('ADD_MANDATE_TO_COMMITTEE', committee)")
     */
    public function addMandateAction(
        Request $request,
        Committee $committee,
        CommitteeManager $committeeManager
    ): Response {
        $newMandateCommand = new CommitteeAdherentMandateCommand($committee);
        $form = $this->createForm(CommitteeMandateCommandType::class, $newMandateCommand, [
            'types' => $committeeManager->getAvailableMandateTypesFor($committee),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $mandate = $this->mandateManager->createMandateFromCommand($newMandateCommand);
                $this->addFlashMsgForNewMandate($mandate);

                return $this->redirectToRoute('app_admin_committee_mandates', ['id' => $committee->getId()]);
            }

            if ($mandates = $this->mandateRepository->findAllActiveMandatesForAdherent($newMandateCommand->getAdherent())) {
                $this->addMandateAdherentWarning($mandates);
            }

            return $this->render('admin/committee/mandates/add_confirm.html.twig', [
                'form' => $form->createView(),
                'committee' => $committee,
            ]);
        }

        return $this->render('admin/committee/mandates/add.html.twig', [
            'form' => $form->createView(),
            'committee' => $committee,
        ]);
    }

    /**
     * @Route("/mandates/{id}/replace", name="app_admin_committee_replace_mandate", methods={"GET|POST"})
     * @Security("has_role('ROLE_ADMIN_COMMITTEES') and is_granted('CHANGE_MANDATE_OF_COMMITTEE', mandate.getCommittee())")
     */
    public function replaceMandateAction(Request $request, CommitteeAdherentMandate $mandate): Response
    {
        $committee = $mandate->getCommittee();
        if ($mandate->getFinishAt()) {
            $this->addFlash('sonata_flash_error', sprintf('Le mandate (id %s) est inactif et ne peut pas être remplacé.', $mandate->getId()));

            return $this->redirectToRoute('app_admin_committee_mandates', ['id' => $committee->getId()]);
        }

        $newMandateCommand = CommitteeAdherentMandateCommand::createFromCommitteeMandate($mandate);
        $form = $this->createForm(CommitteeMandateCommandType::class, $newMandateCommand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $newMandate = $this->mandateManager->replaceMandate($mandate, $newMandateCommand);

                $this->addFlashMsgForClosedMandate($mandate);
                $this->addFlashMsgForNewMandate($newMandate);

                return $this->redirectToRoute('app_admin_committee_mandates', ['id' => $committee->getId()]);
            }

            if ($mandates = $this->mandateRepository->findAllActiveMandatesForAdherent($newMandateCommand->getAdherent())) {
                $this->addMandateAdherentWarning($mandates);
            }

            return $this->render('admin/committee/mandates/replace_confirm.html.twig', [
                'form' => $form->createView(),
                'mandate' => $mandate,
                'committee' => $committee,
            ]);
        }

        return $this->render('admin/committee/mandates/replace.html.twig', [
            'form' => $form->createView(),
            'mandate' => $mandate,
            'committee' => $committee,
        ]);
    }

    /**
     * @Route("/mandates/{id}/close", name="app_admin_committee_close_mandate", methods={"GET|POST"})
     * @Security("has_role('ROLE_ADMIN_COMMITTEES') and is_granted('CHANGE_MANDATE_OF_COMMITTEE', mandate.getCommittee())")
     */
    public function closeMandateAction(
        Request $request,
        CommitteeAdherentMandate $mandate,
        EntityManagerInterface $entityManager
    ): Response {
        $committee = $mandate->getCommittee();
        if ($mandate->getFinishAt()) {
            $this->addFlash('sonata_flash_error', sprintf('Le mandate (id %s) est inactif et ne peut pas être retiré.', $mandate->getId()));

            return $this->redirectToRoute('app_admin_committee_mandates', ['id' => $committee->getId()]);
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $mandate->end(new \DateTime(), AbstractAdherentMandate::REASON_MANUAL);

                $entityManager->flush();

                $this->addFlashMsgForClosedMandate($mandate);
            }

            return $this->redirectToRoute('app_admin_committee_mandates', ['id' => $committee->getId()]);
        }

        return $this->render('admin/committee/mandates/close_confirm.html.twig', [
            'form' => $form->createView(),
            'mandate' => $mandate,
            'committee' => $committee,
        ]);
    }

    /**
     * @Route("/{committee}/members/{adherent}/set-privilege/{privilege}", name="app_admin_committee_change_privilege", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_COMMITTEES')")
     */
    public function changePrivilegeAction(
        CommitteeManager $manager,
        Request $request,
        Committee $committee,
        Adherent $adherent,
        string $privilege
    ): Response {
        if (CommitteeMembership::COMMITTEE_HOST === $privilege
            && !$this->isGranted('PROMOTE_TO_HOST_IN_COMMITTEE', $committee)) {
            $this->createAccessDeniedException('Cannot promote an adherent');
        }

        if (!$this->isCsrfTokenValid(sprintf('committee.change_privilege.%s', $adherent->getId()), $request->query->get('token'))) {
            throw new BadRequestHttpException('Invalid Csrf token provided.');
        }

        try {
            $manager->changePrivilege($adherent, $committee, $privilege);
        } catch (CommitteeMembershipException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_admin_committee_members', [
            'id' => $committee->getId(),
        ]);
    }

    /**
     * @Route("/{committee}/members/{adherent}/{action}-mandate", name="app_admin_committee_change_mandate", methods={"GET"})
     * @Security("has_role('ROLE_ADMIN_COMMITTEE_DESIGNATION')")
     */
    public function changeMandateAction(
        Request $request,
        Committee $committee,
        Adherent $adherent,
        string $action
    ): Response {
        if (!\in_array($action, CommitteeAdherentMandateManager::ACTIONS)) {
            throw new BadRequestHttpException(sprintf('Action "%s" is not authorized.', $action));
        }

        if (!$committee->isApproved()) {
            throw new BadRequestHttpException($this->translator->trans('adherent_mandate.committee.committee_not_approved'));
        }

        if (!$this->isCsrfTokenValid(sprintf('committee.change_mandate.%s', $adherent->getId()), $request->query->get('token'))) {
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

    private function addMandateAdherentWarning(array $mandates): void
    {
        $msg = '';
        /** @var CommitteeAdherentMandate $activeMandate */
        array_walk($mandates, function (CommitteeAdherentMandate $activeMandate) use (&$msg) {
            $msg .= sprintf(
                '%s dans le comité "%s", ',
                CommitteeMandateQualityEnum::SUPERVISOR === $activeMandate->getQuality()
                    ? ($activeMandate->isProvisional() ? 'Animateur provisoire' : 'Animateur')
                    : 'Adhérent désigné',
                $activeMandate->getCommittee()->getName());
        });

        $this->addFlash(
            'warning',
            substr_replace("Attention, cet adhérent est déjà $msg", '.', -2)
        );
    }

    private function addFlashMsgForNewMandate(CommitteeAdherentMandate $mandate): void
    {
        $this->addFlash('sonata_flash_success', sprintf(
            '%s est devenu%s %s.',
            $mandate->getAdherent()->getFullName(),
            $mandate->isFemale() ? 'e' : '',
            $this->translator->trans('adherent_mandate.committee.'.$mandate->getType()))
        );
    }

    private function addFlashMsgForClosedMandate(CommitteeAdherentMandate $mandate): void
    {
        $this->addFlash('sonata_flash_success', sprintf(
            '%s n\'est plus %s.',
            $mandate->getAdherent()->getFullName(),
            $this->translator->trans('adherent_mandate.committee.'.$mandate->getType()))
        );
    }
}

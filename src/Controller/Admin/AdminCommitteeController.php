<?php

namespace App\Controller\Admin;

use App\Admin\Committee\CommitteeAdherentMandateTypeEnum;
use App\Committee\CommitteeAdherentMandateCommand;
use App\Committee\CommitteeAdherentMandateManager;
use App\Committee\CommitteeManagementAuthority;
use App\Committee\CommitteeManager;
use App\Committee\Exception\CommitteeAdherentMandateException;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\Committee;
use App\Exception\BaseGroupException;
use App\Exception\CommitteeMembershipException;
use App\Form\Admin\CommitteeMandateCommandType;
use App\Form\ConfirmActionType;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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
            'can_add_mandate' => \count($this->getAvailableMandateTypesFor($committee)) > 0,
        ]);
    }

    /**
     * @Route("/{id}/mandates/add", name="app_admin_committee_add_mandate", methods={"GET|POST"})
     * @Security("has_role('ROLE_ADMIN_COMMITTEES')")
     */
    public function addMandateAction(Request $request, Committee $committee): Response
    {
        $types = $this->getAvailableMandateTypesFor($committee);
        if (!$types) {
            $this->addFlash('sonata_flash_error', sprintf('Le comité "%s" n\'a pas de mandat disponible.', $committee->getName()));

            return $this->redirectToRoute('app_admin_committee_mandates', ['id' => $committee->getId()]);
        }

        $newMandateCommand = new CommitteeAdherentMandateCommand($committee);
        $form = $this->createForm(CommitteeMandateCommandType::class, $newMandateCommand, ['types' => $types]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $mandate = $this->mandateManager->createMandateFromCommand($newMandateCommand);
                $this->addFlash('sonata_flash_success', \sprintf(
                    'Le mandate %s a été ajouté avec succès.',
                    $this->translator->trans(''.$mandate->getType()))
                );

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
     * @Security("has_role('ROLE_ADMIN_COMMITTEES')")
     */
    public function replaceMandateAction(Request $request, CommitteeAdherentMandate $mandate): Response
    {
        if ($mandate->getFinishAt()) {
            $this->addFlash('sonata_flash_error', sprintf('Le mandate (id %s) est inactif et ne peut pas être remplacé.', $mandate->getId()));

            return $this->redirectToRoute('app_admin_committee_mandates', ['id' => $mandate->getCommittee()->getId()]);
        }

        $newMandateCommand = CommitteeAdherentMandateCommand::createFromCommitteeMandate($mandate);
        $form = $this->createForm(CommitteeMandateCommandType::class, $newMandateCommand);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('confirm')->isClicked()) {
                $this->mandateManager->replaceMandate($mandate, $newMandateCommand);
                $this->addFlash('sonata_flash_success', \sprintf('Le mandat avec l\'id %s a été remplacé avec succès.', $mandate->getId()));

                return $this->redirectToRoute('app_admin_committee_mandates', ['id' => $mandate->getCommittee()->getId()]);
            }

            if ($mandates = $this->mandateRepository->findAllActiveMandatesForAdherent($newMandateCommand->getAdherent())) {
                $this->addMandateAdherentWarning($mandates);
            }

            return $this->render('admin/committee/mandates/replace_confirm.html.twig', [
                'form' => $form->createView(),
                'mandate' => $mandate,
            ]);
        }

        return $this->render('admin/committee/mandates/replace.html.twig', [
            'form' => $form->createView(),
            'mandate' => $mandate,
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
        if (!$committee->isApproved()) {
            throw new BadRequestHttpException('Committee must be approved to change member privileges.');
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

    private function addMandateAdherentWarning(array $mandates): void
    {
        $msg = '';
        /** @var CommitteeAdherentMandate $activeMandate */
        array_walk($mandates, function (CommitteeAdherentMandate $activeMandate) use (&$msg) {
            $msg .= \sprintf(
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

    private function getAvailableMandateTypesFor(Committee $committee): array
    {
        $mandates = $this->mandateRepository->findAllActiveMandatesForCommittee($committee);
        $types = CommitteeAdherentMandateTypeEnum::getTypesForCreation();

        /** @var CommitteeAdherentMandate $mandate */
        array_walk($mandates, function (CommitteeAdherentMandate $mandate) use (&$types) {
            if (false !== $key = array_search($mandate->getType(), $types)) {
                unset($types[$key]);
            }
        });

        return $types;
    }
}

<?php

namespace App\Controller\Admin;

use App\Committee\CommitteeAdherentMandateManager;
use App\Committee\CommitteeManagementAuthority;
use App\Committee\CommitteeManager;
use App\Committee\CommitteeMembershipManager;
use App\Committee\DTO\CommitteeAdherentMandateCommand;
use App\Committee\Exception\CommitteeAdherentMandateException;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\AdherentMandateInterface;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Exception\BaseGroupException;
use App\Exception\CommitteeMembershipException;
use App\Form\Admin\CommitteeMandateCommandType;
use App\Form\ConfirmActionType;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use App\Repository\CommitteeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemPoolInterface;
use DoctrineExtensions\Query\Mysql\Exp;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminCommitteeController extends AbstractController
{
    public function __construct(
        private readonly CommitteeAdherentMandateManager $mandateManager,
        private readonly CommitteeAdherentMandateRepository $mandateRepository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[IsGranted('ROLE_ADMIN_TERRITOIRES_COMMITTEES')]
    #[Route(path: '/carte-des-comites', name: 'app_committees_map', methods: ['GET'])]
    public function committeeMapAction(): Response
    {
        return $this->render('renaissance/committees_map.html.twig');
    }

    #[IsGranted('ROLE_ADMIN_TERRITOIRES_COMMITTEES')]
    #[Route(path: '/comites/perimetres.json', name: 'app_committees_perimeters', methods: ['GET'])]
    public function getCommitteesPerimetersAction(CommitteeRepository $repository, CacheItemPoolInterface $cache): Response
    {
        $item = $cache->getItem('committees_map');

        if (!$item->isHit()) {
            $committees = $repository->getCommitteesPerimeters();
            $geoJson = [
                'type' => 'FeatureCollection',
                'features' => [],
            ];

            foreach ($committees as $committee) {
                $geoJson['features'][] = [
                    'type' => 'Feature',
                    'properties' => [
                        'color' => \sprintf('#%06X', random_int(0, 0xFFFFFF)),
                        'name' => $committee['name'],
                        'animator_first_name' => $committee['animator']['firstName'],
                        'animator_last_name' => $committee['animator']['lastName'],
                        'animator_id' => $committee['animator']['id'],
                        'animator_email' => $committee['animator']['emailAddress'],
                        'id' => $committee['id'],
                    ],
                    'geometry' => json_decode($committee['features'], true),
                ];
            }

            $item->set(base64_encode($responseJson = json_encode($geoJson)));
            $item->expiresAt(new \DateTime('+10hours'));
            $cache->save($item);
        }

        $response = new Response($responseJson ?? base64_decode($item->get()));
        $response->setPublic();

        return $response;
    }

    /**
     * Refuses the committee.
     */
    #[IsGranted('ROLE_ADMIN_TERRITOIRES_COMMITTEES')]
    #[Route(path: '/committee/{id}/refuse', name: 'app_admin_committee_refuse', methods: ['GET|POST'])]
    public function refuseAction(
        Request $request,
        Committee $committee,
        CommitteeManagementAuthority $committeeManagementAuthority,
    ): Response {
        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                try {
                    $committeeManagementAuthority->refuse($committee);
                    $this->addFlash('sonata_flash_success', \sprintf('Le comité « %s » a été refusé avec succès.', $committee->getName()));
                } catch (BaseGroupException $exception) {
                    throw $this->createNotFoundException(\sprintf('Committee %u must be pending in order to be refused.', $committee->getId()), $exception);
                }
            }

            return $this->redirectToRoute('admin_app_committee_list');
        }

        return $this->render('admin/committee/refuse.html.twig', [
            'form' => $form->createView(),
            'object' => $committee,
        ]);
    }

    #[IsGranted('ROLE_ADMIN_TERRITOIRES_COMMITTEES')]
    #[Route(path: '/committee/{id}/members', name: 'app_admin_committee_members', methods: ['GET'])]
    public function membersAction(CommitteeMembershipManager $manager, Committee $committee): Response
    {
        return $this->render('admin/committee/members.html.twig', [
            'committee' => $committee,
            'memberships' => $manager->getCommitteeMemberships($committee),
            'active_mandates_adherent_ids' => $this->mandateRepository->findActiveMandateAdherentIds($committee),
        ]);
    }

    #[IsGranted('ROLE_ADMIN_TERRITOIRES_COMMITTEES')]
    #[Route(path: '/committee/{id}/mandates', name: 'app_admin_committee_mandates', methods: ['GET'])]
    public function mandatesAction(Committee $committee): Response
    {
        return $this->render('admin/committee/mandates/list.html.twig', [
            'committee' => $committee,
        ]);
    }

    #[IsGranted(new Expression("is_granted('ROLE_ADMIN_TERRITOIRES_COMMITTEES') and is_granted('ADD_MANDATE_TO_COMMITTEE', subject)"), 'committee')]
    #[Route(path: '/committee/{id}/mandates/add', name: 'app_admin_committee_add_mandate', methods: ['GET|POST'])]
    public function addMandateAction(
        Request $request,
        Committee $committee,
        CommitteeAdherentMandateManager $committeeAdherentMandateManager,
    ): Response {
        $newMandateCommand = new CommitteeAdherentMandateCommand($committee);
        $form = $this->createForm(CommitteeMandateCommandType::class, $newMandateCommand, [
            'types' => $committeeAdherentMandateManager->getAvailableMandateTypesFor($committee),
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

    #[IsGranted(new Expression("is_granted('ROLE_ADMIN_TERRITOIRES_COMMITTEES') and is_granted('CHANGE_MANDATE_OF_COMMITTEE', subject.getCommittee())"), 'mandate')]
    #[Route(path: '/committee/mandates/{id}/replace', name: 'app_admin_committee_replace_mandate', methods: ['GET|POST'])]
    public function replaceMandateAction(Request $request, CommitteeAdherentMandate $mandate): Response
    {
        $committee = $mandate->getCommittee();
        if ($mandate->getFinishAt()) {
            $this->addFlash('sonata_flash_error', \sprintf('Le mandate (id %s) est inactif et ne peut pas être remplacé.', $mandate->getId()));

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

    #[IsGranted(new Expression("is_granted('ROLE_ADMIN_TERRITOIRES_COMMITTEES') and is_granted('CHANGE_MANDATE_OF_COMMITTEE', subject.getCommittee())"), 'mandate')]
    #[Route(path: '/committee/mandates/{id}/close', name: 'app_admin_committee_close_mandate', methods: ['GET|POST'])]
    public function closeMandateAction(
        Request $request,
        CommitteeAdherentMandate $mandate,
        EntityManagerInterface $entityManager,
    ): Response {
        $committee = $mandate->getCommittee();
        if ($mandate->getFinishAt()) {
            $this->addFlash('sonata_flash_error', \sprintf('Le mandate (id %s) est inactif et ne peut pas être retiré.', $mandate->getId()));

            return $this->redirectToRoute('app_admin_committee_mandates', ['id' => $committee->getId()]);
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $mandate->end(new \DateTime(), AdherentMandateInterface::REASON_MANUAL);

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

    #[IsGranted('ROLE_ADMIN_TERRITOIRES_COMMITTEES')]
    #[Route(path: '/committee/{committee}/members/{adherent}/set-privilege/{privilege}', name: 'app_admin_committee_change_privilege', methods: ['GET'])]
    public function changePrivilegeAction(
        CommitteeManager $manager,
        Request $request,
        Committee $committee,
        Adherent $adherent,
        string $privilege,
    ): Response {
        if (CommitteeMembership::COMMITTEE_HOST === $privilege
            && !$this->isGranted('PROMOTE_TO_HOST_IN_COMMITTEE', $committee)) {
            $this->createAccessDeniedException('Cannot promote an adherent');
        }

        if (!$this->isCsrfTokenValid(\sprintf('committee.change_privilege.%s', $adherent->getId()), $request->query->get('token'))) {
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

    #[IsGranted('ROLE_ADMIN_TERRITOIRES_COMMITTEE_DESIGNATION')]
    #[Route(path: '/committee/{committee}/members/{adherent}/{action}-mandate', name: 'app_admin_committee_change_mandate', methods: ['GET'])]
    public function changeMandateAction(
        Request $request,
        Committee $committee,
        Adherent $adherent,
        string $action,
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

    private function addFlashMsgForNewMandate(CommitteeAdherentMandate $mandate): void
    {
        $this->addFlash('sonata_flash_success', \sprintf(
            '%s est devenu%s %s.',
            $mandate->getAdherent()->getFullName(),
            $mandate->isFemale() ? 'e' : '',
            $this->translator->trans('adherent_mandate.committee.'.$mandate->getType()))
        );
    }

    private function addFlashMsgForClosedMandate(CommitteeAdherentMandate $mandate): void
    {
        $this->addFlash('sonata_flash_success', \sprintf(
            '%s n\'est plus %s.',
            $mandate->getAdherent()->getFullName(),
            $this->translator->trans('adherent_mandate.committee.'.$mandate->getType()))
        );
    }
}

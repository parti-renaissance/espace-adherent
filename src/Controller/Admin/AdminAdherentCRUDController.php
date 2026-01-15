<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Adherent\AdherentExtractCommand;
use App\Adherent\AdherentExtractCommandHandler;
use App\Adherent\BanManager;
use App\Adherent\Certification\CertificationAuthorityManager;
use App\Adherent\Certification\CertificationPermissions;
use App\Adherent\Command\SendResubscribeEmailCommand;
use App\Adherent\Merge\AdherentMergeManager;
use App\Adherent\Merge\ProcessTracker;
use App\Adherent\Tag\Command\RefreshAdherentTagCommand;
use App\Adherent\UnregistrationManager;
use App\Admin\AdherentAdmin;
use App\Entity\Adherent;
use App\Entity\Administrator;
use App\Form\Admin\Adherent\CreateRenaissanceType;
use App\Form\Admin\Adherent\UnregistrationType;
use App\Form\Admin\Adherent\VerifyEmailType;
use App\Form\Admin\AdherentAutocompleteType;
use App\Form\Admin\Extract\AdherentExtractType;
use App\Form\ConfirmActionType;
use App\History\Command\MergeAdherentActionHistoryCommand;
use App\Renaissance\Membership\Admin\AdherentCreateCommandHandler;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Bridge\Exporter\AdminExporter;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class AdminAdherentCRUDController extends CRUDController
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $secret,
    ) {
    }

    public function listAction(Request $request): Response
    {
        $this->admin->checkAccess('list');

        $datagrid = $this->admin->getDatagrid();
        $formView = $datagrid->getForm()->createView();
        $results = $datagrid->getResults();

        $this->primeUsers($results);

        $exportFormats = [];

        if ($this->container->has('sonata.admin.admin_exporter') && $this->admin->hasAccess('export')) {
            $exporter = $this->container->get('sonata.admin.admin_exporter');
            \assert($exporter instanceof AdminExporter);
            $exportFormats = $exporter->getAvailableFormats($this->admin);
        }

        return $this->renderWithExtraParams($this->admin->getTemplateRegistry()->getTemplate('list'), [
            'action' => 'list',
            'form' => $formView,
            'datagrid' => $datagrid,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
            'export_formats' => $exportFormats,
        ]);
    }

    public function refreshTagsAction(Request $request, MessageBusInterface $bus): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->admin->getSubject();

        $this->admin->checkAccess('refresh_tags', $adherent);

        $bus->dispatch(new RefreshAdherentTagCommand($adherent->getUuid()));

        $this->addFlash('success', 'Les tags ont été rafraichis avec succès.');

        return $this->redirectTo($request, $adherent);
    }

    public function banAction(Request $request, BanManager $adherentManagementAuthority): Response
    {
        $adherent = $this->admin->getSubject();

        $this->admin->checkAccess('ban', $adherent);

        if (!$adherentManagementAuthority->canBan($adherent)) {
            $this->addFlash(
                'error',
                'Il est possible d\'exclure uniquement les adhérents sans aucun rôle (animateur, référent etc.).'
            );

            $this->addFlash(
                'error',
                \sprintf('Merci de retirer les rôles suivants : %s', implode(', ', array_intersect($adherent->getRoles(), $adherentManagementAuthority->getBlockedRoles())))
            );

            return $this->redirectTo($request, $adherent);
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $adherentManagementAuthority->ban($adherent, $this->getUser());

                $this->addFlash('success', \sprintf('L\'adhérent <b>%s</b> a bien été exclu.', $adherent->getFullName()));
            }

            return $this->redirectToList();
        }

        return $this->renderWithExtraParams('admin/adherent/ban.html.twig', [
            'form' => $form->createView(),
            'object' => $adherent,
            'action' => 'ban',
            'elements' => $this->admin->getShow(),
        ]);
    }

    public function terminateMembershipAction(Request $request, UnregistrationManager $unregistrationManager): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->admin->getSubject();

        $this->admin->checkAccess('terminate_membership', $adherent);

        if (!$this->isGranted('UNREGISTER', $adherent)) {
            $this->addFlash(
                'error',
                'Il est possible de faire désadhérer uniquement les adhérents sans aucun rôle (animateur, référent, candidat etc.).'
            );

            return $this->redirectTo($request, $adherent);
        }

        $unregistrationCommand = $unregistrationManager->createUnregistrationCommand($this->getUser());

        $form = $this
            ->createForm(UnregistrationType::class, $unregistrationCommand)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $unregistrationManager->terminateMembership($adherent, $unregistrationCommand);

            $this->addFlash('success', \sprintf(
                'L\'adhérent <b>%s</b> (%s) a bien été supprimé.',
                $adherent->getFullName(),
                $adherent->getEmailAddress()
            ));

            return $this->redirectToList();
        }

        return $this->renderWithExtraParams('admin/adherent/terminate_membership.html.twig', [
            'form' => $form->createView(),
            'object' => $adherent,
            'action' => 'terminate_membership',
        ]);
    }

    public function certifyAction(Request $request, CertificationAuthorityManager $certificationManager): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->admin->getSubject();

        $this->admin->checkAccess('certify', $adherent);

        if (!$this->isGranted(CertificationPermissions::CERTIFY, $adherent)) {
            $this->addFlash('error', \sprintf(
                'L\'adhérent <b>%s</b> (%s) est déjà certifié.',
                $adherent->getFullName(),
                $adherent->getEmailAddress()
            ));

            return $this->redirectTo($request, $adherent);
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $certificationManager->certify($adherent, $this->getUser());

                $this->addFlash('success', \sprintf(
                    'L\'adhérent <b>%s</b> (%s) a bien été certifié.',
                    $adherent->getFullName(),
                    $adherent->getEmailAddress()
                ));
            }

            return $this->redirectTo($request, $adherent);
        }

        return $this->renderWithExtraParams('admin/adherent/certify.html.twig', [
            'form' => $form->createView(),
            'object' => $adherent,
            'action' => 'certify',
            'elements' => $this->admin->getShow(),
        ]);
    }

    public function uncertifyAction(Request $request, CertificationAuthorityManager $certificationManager): Response
    {
        /** @var Adherent $adherent */
        $adherent = $this->admin->getSubject();

        $this->admin->checkAccess('uncertify', $adherent);

        if (!$this->isGranted(CertificationPermissions::UNCERTIFY, $adherent)) {
            $this->addFlash('error', \sprintf(
                'L\'adhérent <b>%s</b> (%s) n\'est pas certifié.',
                $adherent->getFullName(),
                $adherent->getEmailAddress()
            ));

            return $this->redirectTo($request, $adherent);
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('allow')->isClicked()) {
                $certificationManager->uncertify($adherent, $this->getUser());

                $this->addFlash('warning', \sprintf(
                    'L\'adhérent <b>%s</b> (%s) n\'est plus certifié.',
                    $adherent->getFullName(),
                    $adherent->getEmailAddress()
                ));
            }

            return $this->redirectTo($request, $adherent);
        }

        return $this->renderWithExtraParams('admin/adherent/uncertify.html.twig', [
            'form' => $form->createView(),
            'object' => $adherent,
            'action' => 'uncertify',
            'elements' => $this->admin->getShow(),
        ]);
    }

    public function extractAction(
        Request $request,
        AdherentExtractCommandHandler $adherentExtractCommandHandler,
    ): Response {
        $this->admin->isGranted('ROLE_ADMIN_ADHERENT_EXTRACT');

        $adherentExtractCommand = new AdherentExtractCommand();

        $form = $this
            ->createForm(AdherentExtractType::class, $adherentExtractCommand)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            return $adherentExtractCommandHandler->createResponse($adherentExtractCommand);
        }

        return $this->render('admin/adherent/extract/request.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function sendResubscribeEmailAction(Request $request, Adherent $adherent, MessageBusInterface $bus): Response
    {
        if ($request->isMethod(Request::METHOD_POST)) {
            $this->validateCsrfToken($request, 'admin.adherent.send_email');

            $bus->dispatch(new SendResubscribeEmailCommand($adherent));

            $this->addFlash('sonata_flash_success', 'Email a bien été envoyé');

            return $this->redirect($this->admin->generateObjectUrl('edit', $adherent));
        }

        return $this->renderWithExtraParams('admin/CRUD/confirm.html.twig', [
            'csrf_token' => $this->getCsrfToken('admin.adherent.send_email'),
            'action' => 'send_resubscribe_email',
            'message' => \sprintf(
                'Êtes-vous sûr de vouloir envoyer un email de réabonnement à <b>%s</b> (%s) ?',
                $adherent->getFullName(),
                $adherent->getEmailAddress()
            ),
            'object' => $adherent,
            'cancel_action' => 'edit',
        ]);
    }

    public function createRenaissanceVerifyEmailAction(
        Request $request,
        AdherentCreateCommandHandler $adherentCreateCommandHandler,
    ): Response {
        $this->admin->checkAccess('create_renaissance_verify_email');

        $adherentCreateCommand = $adherentCreateCommandHandler->createCommand();

        $form = $this
            ->createForm(VerifyEmailType::class, $adherentCreateCommand)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirect($this->admin->generateUrl('create_renaissance', ['email_address' => $adherentCreateCommand->getEmailAddress()]));
        }

        return $this->renderWithExtraParams('admin/adherent/renaissance/verify_email.html.twig', [
            'action' => 'create_adherent_verify_email',
            'object' => $adherentCreateCommand,
            'form' => $form->createView(),
        ]);
    }

    public function createRenaissanceAction(
        Request $request,
        AdherentCreateCommandHandler $adherentCreateCommandHandler,
    ): Response {
        $this->admin->checkAccess('create_renaissance');

        if (!$email = $request->query->get('email_address')) {
            $this->addFlash('sonata_flash_error', 'Le paramètre email_address est manquant ou invalide');

            return $this->redirect($this->admin->generateUrl('create_renaissance_verify_email'));
        }

        $command = $adherentCreateCommandHandler->createCommand();
        $command->email = $email;

        if ($adherent = $this->adherentRepository->findOneByEmail($command->getEmailAddress())) {
            $command->updateFromAdherent($adherent);
        }

        $form = $this
            ->createForm(CreateRenaissanceType::class, $command, [
                'from_certified_adherent' => $command->isCertified(),
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $adherentCreateCommandHandler->handle($command, $this->getUser(), $adherent);

            $this->addFlash('sonata_flash_success', \sprintf(
                'Le compte adhérent Renaissance <b>%s %s</b> (%s) a bien été créé.',
                $command->firstName,
                $command->lastName,
                $command->email
            ));

            return $this->redirect($this->admin->generateUrl('create_renaissance_verify_email'));
        }

        return $this->renderWithExtraParams('admin/adherent/renaissance/create.html.twig', [
            'action' => 'create_renaissance',
            'object' => $command,
            'form' => $form->createView(),
        ]);
    }

    public function mergeAction(int $id, Request $request, AdherentMergeManager $mergeManager): Response
    {
        $this->admin->checkAccess('merge');

        /* @var Adherent $adherentSource */
        if (!$adherentSource = $this->admin->getObject($id)) {
            $this->addFlash('sonata_flash_info', 'Adhérent source est introuvable ou déjà fusionné.');

            return $this->redirect($this->admin->generateUrl('list'));
        }

        if ($mergeManager->mergeAlreadyStarted($adherentSource)) {
            $this->addFlash('sonata_flash_error', 'Impossible de fusionner : cet adhérent est déjà en cours de fusion.');

            return $this->redirect($this->admin->generateUrl('list'));
        }

        $form = $this->createFormBuilder(null, ['validation_groups' => ['Admin:merge']])
            ->add('adherent', AdherentAutocompleteType::class, [
                'label' => 'Adhérent cible',
                'model_manager' => $this->admin->getModelManager(),
                'constraints' => [
                    new NotBlank(groups: ['Admin:merge']),
                    new Callback(function ($object, ExecutionContextInterface $context) use ($adherentSource) {
                        if (null === $object) {
                            return;
                        }

                        if ($object->getId() === $adherentSource->getId()) {
                            $context
                                ->buildViolation('Vous ne pouvez pas fusionner un adhérent avec lui-même.')
                                ->addViolation()
                            ;
                        }
                    }, ['Admin:merge']),
                ],
            ])
            ->add('confirm', SubmitType::class, [
                'label' => 'Confirmer l\'adhérent cible',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->getForm()
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirect($this->admin->generateObjectUrl('merge_confirme', $adherentSource, [
                'adherentTarget' => $adherentTargetId = $form->get('adherent')->getData()->getId(),
                's' => $this->generateMergeToken($adherentSource->getId(), $adherentTargetId),
            ]));
        }

        return $this->render('admin/adherent/renaissance/merge.html.twig', [
            'adherent_source' => $adherentSource,
            'form' => $form->createView(),
        ]);
    }

    public function mergeConfirmeAction(
        #[CurrentUser] Administrator $administrator,
        Adherent $adherentSource,
        Adherent $adherentTarget,
        Request $request,
        AdherentMergeManager $mergeManager,
        MessageBusInterface $messageBus,
    ): Response {
        $this->admin->checkAccess('merge');

        if ($mergeManager->mergeAlreadyStarted($adherentSource)) {
            $this->addFlash('sonata_flash_warning', 'Impossible de fusionner : cet adhérent est déjà en cours de fusion.');

            return $this->redirect($this->admin->generateUrl('list'));
        }

        $expectedToken = $this->generateMergeToken($adherentSource->getId(), $adherentTarget->getId());

        if (!hash_equals($expectedToken, (string) $request->query->get('s'))) {
            $this->addFlash('sonata_flash_error', 'Lien invalide ou modifié. Merci de repasser par la recherche.');

            return $this->redirect($this->admin->generateObjectUrl('merge', $adherentSource));
        }

        $form = $this
            ->createForm(ConfirmActionType::class)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('deny')->isClicked()) {
                $this->addFlash('sonata_flash_info', 'La fusion a été annulée.');

                return $this->redirect($this->admin->generateObjectUrl('merge', $adherentSource));
            }

            if ($form->get('allow')->isClicked()) {
                $mergeManager->handleMergeRequest($adherentSource, $adherentTarget);

                $messageBus->dispatch(MergeAdherentActionHistoryCommand::create($administrator, $adherentSource, $adherentTarget));

                return $this->redirect($this->admin->generateObjectUrl('merge_status', $adherentSource, ['target_id' => $adherentTarget->getId()]));
            }
        }

        return $this->render('admin/adherent/renaissance/merge_confirme.html.twig', [
            'adherent_source' => $adherentSource,
            'adherent_target' => $adherentTarget,
            'form' => $form->createView(),
        ]);
    }

    public function mergeStatusAction(int $id, Request $request, ProcessTracker $processTracker): Response
    {
        $this->admin->checkAccess('merge');

        $adherentSource = $this->admin->getObject($id);
        $history = $processTracker->getHistory((string) $id);

        if (!$adherentSource && !$history) {
            $this->addFlash('sonata_flash_info', 'Adhérent source est introuvable ou déjà fusionné.');

            return $this->redirect($this->admin->generateUrl('list'));
        }

        return $this->render('admin/adherent/renaissance/merge_status.html.twig', [
            'adherent_source' => $adherentSource,
            'adherent_source_id' => $id,
            'adherent_target' => $this->admin->getObject((int) $request->query->get('target_id')),
            'history' => $history,
        ]);
    }

    public function autocompleteSearchAction(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_APP_ADMIN_ADHERENT_SEARCH');

        $searchText = $request->query->get('q', '');
        if (!\is_string($searchText) || mb_strlen($searchText, 'UTF-8') < 1) {
            return new JsonResponse(['status' => 'KO', 'message' => 'Too short search string.'], Response::HTTP_BAD_REQUEST);
        }

        $datagrid = $this->admin->getDatagrid();

        if ($datagrid->hasFilter('search')) {
            $datagrid->setValue('search', null, $searchText);
        } else {
            throw new BadRequestHttpException('Le filtre "search" n\'est pas configuré dans AdherentAdmin.');
        }

        if ($filterMethod = $request->query->get(AdherentAdmin::ADHERENT_AUTOCOMPLETE_FILTER_METHOD_PARAM_NAME)) {
            if (!str_starts_with($filterMethod, 'autocompleteCallback')) {
                throw new BadRequestHttpException('Invalid filter method name.');
            }

            if (!method_exists($this->admin, $filterMethod)) {
                throw new BadRequestHttpException(\sprintf('Method "%s" not found in %s', $filterMethod, \get_class($this->admin)));
            }

            $this->admin->{$filterMethod}($datagrid->getQuery());
        }

        $itemsPerPage = (int) $request->query->get('_per_page', 10);
        $page = (int) $request->query->get('_page', 1);

        $datagrid->setValue(DatagridInterface::PER_PAGE, null, $itemsPerPage);
        $datagrid->setValue(DatagridInterface::PAGE, null, $page);

        $datagrid->buildPager();
        $pager = $datagrid->getPager();

        $items = [];
        $results = $pager->getCurrentPageResults();

        foreach ($results as $adherent) {
            $label = \sprintf(
                '%s (%s) [%s]',
                $adherent->getFullName(),
                $adherent->getEmailAddress(),
                $adherent->getPublicId()
            );

            if ($adherent->isRenaissanceAdherent()) {
                $label .= ' <span class="label label-primary" style="margin-left: 4px; background-color: #00205F; color: white; padding: 2px 5px;">Adhérent</span>';
            } else {
                $label .= ' <span class="label label-info" style="margin-left: 4px; background-color: #73C0F1; color: white; padding: 2px 5px;">Sympathisant</span>';
            }

            $items[] = [
                'id' => $adherent->getId(),
                'label' => $label,
            ];
        }

        return new JsonResponse([
            'status' => 'OK',
            'more' => \count($items) > 0 && !$pager->isLastPage(),
            'items' => $items,
        ]);
    }

    private function generateMergeToken(int $adherentSourceId, int $adherentTargetId): string
    {
        return substr(hash('sha256', $adherentSourceId.'|'.$adherentTargetId.'|'.$this->secret), 0, 15);
    }

    private function primeUsers(iterable $users): void
    {
        $ids = [];
        foreach ($users as $user) {
            $ids[] = $user->getId();
        }

        if (empty($ids)) {
            return;
        }

        $this->entityManager->createQueryBuilder()
            ->select('u, _static_labels, _delegated_access, _zone_based_role, _zone_based_role_zone, _subscription_type', '_adherent_zones')
            ->addSelect('u, _agora_membership, _adherent_mandate, _agora_president, _agora_general_secretary, _committee_membership, _committee_membership_committee, _animator_committees')
            ->from(Adherent::class, 'u')
            ->leftJoin('u.staticLabels', '_static_labels')
            ->leftJoin('u.receivedDelegatedAccesses', '_delegated_access')
            ->leftJoin('u.zoneBasedRoles', '_zone_based_role')
            ->leftJoin('_zone_based_role.zones', '_zone_based_role_zone')
            ->leftJoin('u.subscriptionTypes', '_subscription_type')
            ->leftJoin('u.zones', '_adherent_zones')
            ->leftJoin('u.agoraMemberships', '_agora_membership')
            ->leftJoin('u.presidentOfAgoras', '_agora_president')
            ->leftJoin('u.generalSecretaryOfAgoras', '_agora_general_secretary')
            ->leftJoin('u.committeeMembership', '_committee_membership')
            ->leftJoin('_committee_membership.committee', '_committee_membership_committee')
            ->leftJoin('u.animatorCommittees', '_animator_committees')
            ->leftJoin('u.adherentMandates', '_adherent_mandate')
            ->where('u.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult()
        ;
    }
}

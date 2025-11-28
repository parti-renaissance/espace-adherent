<?php

namespace App\Controller\Admin;

use App\Adherent\AdherentExtractCommand;
use App\Adherent\AdherentExtractCommandHandler;
use App\Adherent\BanManager;
use App\Adherent\Certification\CertificationAuthorityManager;
use App\Adherent\Certification\CertificationPermissions;
use App\Adherent\Command\SendResubscribeEmailCommand;
use App\Adherent\Tag\Command\RefreshAdherentTagCommand;
use App\Adherent\UnregistrationManager;
use App\Entity\Adherent;
use App\Form\Admin\Adherent\CreateRenaissanceType;
use App\Form\Admin\Adherent\UnregistrationType;
use App\Form\Admin\Adherent\VerifyEmailType;
use App\Form\Admin\Extract\AdherentExtractType;
use App\Form\ConfirmActionType;
use App\Renaissance\Membership\Admin\AdherentCreateCommandHandler;
use App\Repository\AdherentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Bridge\Exporter\AdminExporter;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;

class AdminAdherentCRUDController extends CRUDController
{
    public function __construct(
        private readonly AdherentRepository $adherentRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function listAction(Request $request): Response
    {
        $this->admin->checkAccess('list');

        $datagrid = $this->admin->getDatagrid();
        $formView = $datagrid->getForm()->createView();
        $results = $datagrid->getResults();

        $this->primeUsers($results);

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
            'export_formats' => $exportFormats ?? $this->admin->getExportFormats(),
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

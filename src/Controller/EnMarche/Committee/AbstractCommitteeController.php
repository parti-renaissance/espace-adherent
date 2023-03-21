<?php

namespace App\Controller\EnMarche\Committee;

use App\Committee\CommandHandler\CommitteeCreationCommandHandler;
use App\Committee\CommandHandler\CommitteeUpdateCommandHandler;
use App\Committee\CommitteeManager;
use App\Committee\DTO\CommitteeCommand;
use App\Committee\DTO\CommitteeCreationCommand;
use App\Committee\Filter\CommitteeDesignationsListFilter;
use App\Committee\Filter\CommitteeListFilter;
use App\Controller\EnMarche\AccessDelegatorTrait;
use App\Entity\Committee;
use App\Form\Committee\CommitteeDesignationsListFilterType;
use App\Form\Committee\CommitteeFilterType;
use App\Form\CommitteeCommandType;
use App\Geo\ManagedZoneProvider;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeCandidacyRepository;
use App\Repository\CommitteeElectionRepository;
use App\Repository\CommitteeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

abstract class AbstractCommitteeController extends AbstractController
{
    use AccessDelegatorTrait;

    #[Route(path: '', name: 'committees', methods: ['GET', 'POST'])]
    public function committeesAction(
        Request $request,
        CommitteeRepository $committeeRepository,
        ManagedZoneProvider $managedZoneProvider
    ): Response {
        $managedZones = $managedZoneProvider->getManagedZones($this->getMainUser($request->getSession()), $this->getSpaceType());
        $filter = new CommitteeListFilter($managedZones);

        $form = $this->createForm(CommitteeFilterType::class, $filter, [
            'space_type' => $this->getSpaceType(),
        ])->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = new CommitteeListFilter($managedZones);
        }

        return $this->renderTemplate($this->getSpaceType().'/committee/list.html.twig', [
            'committees' => $committeeRepository->searchByFilter($filter),
            'pending_count' => $committeeRepository->countRequestsForZones($managedZones, Committee::PENDING),
            'form' => $form->createView(),
            'total_count' => $committeeRepository->countForZones($managedZones),
        ]);
    }

    #[Route(path: '/demandes', name: 'committees_requests', methods: ['GET', 'POST'])]
    public function committeesRequestsAction(
        Request $request,
        CommitteeRepository $committeeRepository,
        ManagedZoneProvider $managedZoneProvider
    ): Response {
        $managedZones = $managedZoneProvider->getManagedZones($this->getMainUser($request->getSession()), $this->getSpaceType());
        $filter = new CommitteeListFilter($managedZones);

        $form = $this->createForm(CommitteeFilterType::class, $filter, [
            'space_type' => $this->getSpaceType(),
        ])->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = new CommitteeListFilter($managedZones);
        }

        return $this->renderTemplate($this->getSpaceType().'/committee/requests_list.html.twig', [
            'committees' => $committeeRepository->searchRequestsByFilter($filter),
            'form' => $form->createView(),
            'total_count' => $committeeRepository->countRequestsForZones($managedZones),
        ]);
    }

    #[Route(path: '/creer', name: 'create_committee', methods: ['GET', 'POST'])]
    #[IsGranted('CREATE_COMMITTEE')]
    public function createCommitteeAction(Request $request, CommitteeCreationCommandHandler $commandHandler): Response
    {
        $command = CommitteeCreationCommand::createFromAdherent($user = $this->getUser());
        $form = $this->createForm(CommitteeCommandType::class, $command, [
            'with_provisional' => $this->getWithProvisionalSupervisors(),
            'space_type' => $this->getSpaceType(),
            'validation_groups' => ['Default', 'with_provisional_supervisors'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commandHandler->handle($command);
            $this->addFlash('info', 'committee.creation.success.referent');

            return $this->redirectToRoute('app_'.$this->getSpaceType().'_committees_requests');
        }

        return $this->renderTemplate($this->getSpaceType().'/committee/create.html.twig', [
            'form' => $form->createView(),
            'adherent' => $user,
        ]);
    }

    #[Route(path: '/animateur-provisoire-autocompletion', name: 'provisional_supervisor_autocomplete', condition: 'request.isXmlHttpRequest()', methods: ['GET'])]
    public function provisionalSupervisorAutocomplete(
        Request $request,
        AdherentRepository $adherentRepository,
        ManagedZoneProvider $managedZoneProvider
    ): JsonResponse {
        $adherents = $adherentRepository->findForProvisionalSupervisorAutocomplete(
            $request->query->get('name'),
            $request->query->get('gender'),
            $managedZoneProvider->getManagedZones($this->getMainUser($request->getSession()), $this->getSpaceType())
        );

        return $this->json($adherents, Response::HTTP_OK);
    }

    #[Route(path: '/designations', name: 'committees_designations', methods: ['GET'])]
    public function committeesDesignationListAction(
        Request $request,
        ManagedZoneProvider $managedZoneProvider,
        CommitteeElectionRepository $committeeElectionRepository
    ): Response {
        $managedZones = $managedZoneProvider->getManagedZones($this->getMainUser($request->getSession()), $this->getSpaceType());
        $filter = new CommitteeDesignationsListFilter($managedZones);

        $form = $this->createForm(CommitteeDesignationsListFilterType::class, $filter, [
            'space_type' => $this->getSpaceType(),
        ])->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = new CommitteeDesignationsListFilter($managedZones);
        }

        return $this->renderTemplate($this->getSpaceType().'/committee/designations_list.html.twig', [
            'elections' => $committeeElectionRepository->findElections($filter, $request->query->getInt('page', 1)),
            'form' => $form->createView(),
            'filter' => $filter,
        ]);
    }

    #[Route(path: '/designations/{uuid}/candidatures', name: 'committee_candidacy_list', methods: ['GET'])]
    public function committeeCandidaturesListAction(
        Committee $committee,
        CommitteeCandidacyRepository $repository
    ): Response {
        if (!$election = $committee->getCurrentElection()) {
            $this->addFlash('error', 'Une erreur est survenue');

            return $this->redirectToRoute(sprintf('app_%s_committees_designations', $this->getSpaceType()));
        }

        return $this->render('committee/candidacy/candidacy_list.html.twig', [
            'candidacies' => $repository->findAllConfirmedForElection($election),
            'election' => $election,
            'committee' => $committee,
            'designation' => $election->getDesignation(),
            'back_route' => sprintf('app_%s_committees_designations', $this->getSpaceType()),
        ]);
    }

    #[Route(path: '/designations/partielles', name: 'committees_designations_partials', methods: ['GET'])]
    public function listAvailableCommitteePartialAction(
        Request $request,
        ManagedZoneProvider $managedZoneProvider,
        CommitteeRepository $committeeRepository
    ): Response {
        $managedZones = $managedZoneProvider->getManagedZones(
            $this->getMainUser($request->getSession()),
            $this->getSpaceType()
        );
        $filter = new CommitteeDesignationsListFilter($managedZones);

        $form = $this->createForm(CommitteeDesignationsListFilterType::class, $filter, [
            'space_type' => $this->getSpaceType(),
        ])->handleRequest($request);

        if ($form->isSubmitted() && !$form->isValid()) {
            $filter = new CommitteeDesignationsListFilter($managedZones);
        }

        return $this->renderTemplate($this->getSpaceType().'/committee/available_committees_for_partials.html.twig', [
            'committees' => $committeeRepository->findAvailableForPartials($filter, $request->query->getInt('page', 1)),
            'form' => $form->createView(),
            'filter' => $filter,
        ]);
    }

    #[Route(path: '/{slug}/pre-approuver', name: 'pre_approve', methods: ['GET|POST'])]
    #[IsGranted('PRE_APPROVE_COMMITTEE', subject: 'committee')]
    public function preAcceptAction(
        Request $request,
        Committee $committee,
        CommitteeManager $manager,
        CommitteeUpdateCommandHandler $commandHandler
    ): Response {
        $command = CommitteeCommand::createFromCommittee($committee);
        $form = $this->createForm(CommitteeCommandType::class, $command, [
            'with_provisional' => $this->getWithProvisionalSupervisors(),
            'space_type' => $this->getSpaceType(),
            'validation_groups' => ['Default', 'with_provisional_supervisors'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commandHandler->handleForPreApprove($command);
            $this->addFlash('info', 'committee.pre-approve.success');

            return $this->redirectToRoute('app_'.$this->getSpaceType().'_committees_requests');
        }

        return $this->renderTemplate($this->getSpaceType().'/committee/pre_approve.html.twig', [
            'form' => $form->createView(),
            'committee' => $committee,
            'committee_hosts' => $manager->getCommitteeHosts($committee),
        ]);
    }

    #[Route(path: '/{slug}/pre-refuser', name: 'pre_refuse', methods: ['GET|POST'])]
    #[IsGranted('PRE_REFUSE_COMMITTEE', subject: 'committee')]
    public function preRefuseAction(Committee $committee, EntityManagerInterface $manager): Response
    {
        if ($committee->isPreRefused()) {
            $this->addFlash('error', 'Le comité a déjà été pre-refusé.');
        } else {
            $committee->preRefused();
            $manager->flush();

            $this->addFlash('info', 'Le comité a bien été pre-refusé.');
        }

        return $this->redirectToRoute('app_'.$this->getSpaceType().'_committees_requests');
    }

    protected function renderTemplate(string $template, array $parameters = []): Response
    {
        return $this->render($template, array_merge(
            $parameters,
            [
                'base_template' => sprintf('committee/_base_%s.html.twig', $spaceName = $this->getSpaceType()),
                'space_type' => $spaceName,
            ]
        ));
    }

    abstract protected function getSpaceType(): string;

    abstract protected function getWithProvisionalSupervisors(): bool;
}

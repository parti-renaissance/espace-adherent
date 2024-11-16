<?php

namespace App\Controller\EnMarche;

use App\Address\GeoCoder;
use App\Committee\CommandHandler\CommitteeUpdateCommandHandler;
use App\Committee\CommitteeManager;
use App\Committee\DTO\CommitteeCommand;
use App\Committee\Filter\ListFilterObject;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Event\EventCommand;
use App\Event\EventCommandHandler;
use App\Exception\CommitteeMembershipException;
use App\Form\CommitteeCommandType;
use App\Form\CommitteeMemberFilterType;
use App\Form\EventCommandType;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeMembershipRepository;
use Sonata\Exporter\Exporter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[IsGranted('HOST_COMMITTEE', subject: 'committee')]
#[Route(path: '/comites/{slug}')]
class CommitteeManagerController extends AbstractController
{
    private $manager;

    public function __construct(CommitteeManager $manager)
    {
        $this->manager = $manager;
    }

    #[Route(path: '/editer', name: 'app_committee_manager_edit', methods: ['GET', 'POST'])]
    public function editAction(
        Request $request,
        Committee $committee,
        CommitteeUpdateCommandHandler $commandHandler,
    ): Response {
        $command = CommitteeCommand::createFromCommittee($committee);
        $form = $this->createForm(CommitteeCommandType::class, $command, [
            'with_social_networks' => true,
            'validation_groups' => ['Default', 'edit'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commandHandler->handle($command);
            $this->addFlash('info', 'committee.update.success');

            return $this->redirectToRoute('app_committee_manager_edit', [
                'slug' => $committee->getSlug(),
            ]);
        }

        return $this->render('committee_manager/edit.html.twig', [
            'form' => $form->createView(),
            'committee' => $committee,
            'committee_hosts' => $this->manager->getCommitteeHosts($committee),
        ]);
    }

    #[IsGranted(new Expression('committee.isApproved()'))]
    #[Route(path: '/evenements/ajouter', name: 'app_committee_manager_add_event', methods: ['GET', 'POST'])]
    public function addEventAction(
        Request $request,
        Committee $committee,
        GeoCoder $geoCoder,
        EventCommandHandler $eventCommandHandler,
    ): Response {
        $command = new EventCommand($this->getUser(), $committee);
        $command->setTimeZone($geoCoder->getTimezoneFromIp($request->getClientIp()));

        $form = $this
            ->createForm(EventCommandType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $eventCommandHandler->handle($command);

            $this->addFlash('info', 'committee.event.creation.success');

            return $this->redirectToRoute('app_committee_event_show', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('committee_manager/add_event.html.twig', [
            'committee' => $committee,
            'committee_hosts' => $this->manager->getCommitteeHosts($committee),
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted(new Expression('committee.isApproved()'))]
    #[Route(path: '/membres', name: 'app_committee_manager_list_members', methods: ['GET'])]
    public function listMembersAction(
        Request $request,
        Committee $committee,
        CommitteeMembershipRepository $repository,
        AdherentRepository $adherentRepository,
        SerializerInterface $serializer,
        Exporter $exporter,
    ): Response {
        /** @var Adherent $adherent */
        $adherent = $this->getUser();
        $form = $this
            ->createForm(CommitteeMemberFilterType::class, $filter = new ListFilterObject(), [
                'method' => 'GET',
                'csrf_protection' => false,
                'is_supervisor' => $adherent->isSupervisorOf($committee, false),
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('info', 'Le formulaire est invalide');

            return $this->redirectToRoute('app_committee_manager_list_members', ['slug' => $committee->getSlug()]);
        }

        if ($request->query->has('export')) {
            return $exporter->getResponse('xlsx', 'membres-du-comite', new \ArrayIterator(array_map(fn (CommitteeMembership $cm) => [
                'Prénom' => $cm->getAdherent()->getFirstName(),
                'Nom' => $cm->getAdherent()->getLastName(),
                'Age' => $cm->getAdherent()->getLastName(),
                'Code postal' => $cm->getAdherent()->getPostalCode(),
                'Ville' => $cm->getAdherent()->getCityName(),
                'Date d\'adhésion' => $cm->getAdherent()->getRegisteredAt()->format('d/m/Y'),
                'A rejoint le comité le' => $cm->getSubscriptionDate()->format('d/m/Y'),
            ], $repository->getCommitteeMembershipsPaginator(
                $committee,
                $filter,
                $request->query->getInt('page', 1),
                null // without limit
            ))));
        }

        return $this->render('committee_manager/list_members.html.twig', [
            'form' => $form->createView(),
            'committee' => $committee,
            'filter' => $filter,
            'total_member_count' => $repository->countMembers($committee, CommitteeMembership::PRIVILEGES),
            'committee_hosts' => $adherentRepository->findCommitteeHosts($committee),
            'members' => $repository->getCommitteeMembershipsPaginator(
                $committee,
                $filter,
                $request->query->getInt('page', 1)
            ),
        ]);
    }

    #[IsGranted(new Expression("is_granted('SUPERVISE_COMMITTEE', committee) and is_granted('PROMOTE_TO_HOST_IN_COMMITTEE', committee)"))]
    #[Route(path: '/promouvoir-suppleant/{member_uuid}', name: 'app_committee_promote_host', methods: ['GET', 'POST'])]
    public function promoteHostAction(
        Request $request,
        Committee $committee,
        #[MapEntity(expr: 'repository.findOneByUuid(member_uuid)')]
        Adherent $member,
    ): Response {
        if (!$this->manager->isPromotableHost($member, $committee)) {
            throw $this->createNotFoundException(\sprintf('Member "%s" of committee "%s" can not be promoted as a host privileged person.', $member->getUuid(), $committee->getUuid()));
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->manager->promote($member, $committee);

                $this->addFlash('info', 'committee.promote_host.success');
            } catch (CommitteeMembershipException $e) {
                $this->addFlash('error', $e->getMessage());
            }

            return $this->redirectToRoute('app_committee_manager_list_members', [
                'slug' => $committee->getSlug(),
            ]);
        }

        return $this->render('committee_manager/promote_host.html.twig', [
            'member' => $member,
            'committee' => $committee,
            'committee_hosts' => $this->manager->getCommitteeHosts($committee),
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('SUPERVISE_COMMITTEE', subject: 'committee')]
    #[Route(path: '/retirer-suppleant/{member_uuid}', name: 'app_committee_demote_host', methods: ['GET', 'POST'])]
    public function demoteHostAction(
        Request $request,
        Committee $committee,
        #[MapEntity(expr: 'repository.findOneByUuid(member_uuid)')]
        Adherent $member,
    ): Response {
        if (!$this->manager->isDemotableHost($member, $committee)) {
            throw $this->createNotFoundException(\sprintf('Member "%s" of committee "%s" can not be demoted as a simple follower.', $member->getUuid(), $committee->getUuid()));
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->demote($member, $committee);
            $this->addFlash('info', 'committee.demote_host.success');

            return $this->redirectToRoute('app_committee_manager_list_members', [
                'slug' => $committee->getSlug(),
            ]);
        }

        return $this->render('committee_manager/demote_host.html.twig', [
            'member' => $member,
            'committee' => $committee,
            'committee_hosts' => $this->manager->getCommitteeHosts($committee),
            'form' => $form->createView(),
        ]);
    }
}

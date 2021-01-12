<?php

namespace App\Controller\EnMarche;

use App\Address\GeoCoder;
use App\Committee\CommitteeCommand;
use App\Committee\CommitteeManager;
use App\Committee\CommitteeUpdateCommandHandler;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Event\EventCommand;
use App\Event\EventCommandHandler;
use App\Event\EventRegistrationCommand;
use App\Event\EventRegistrationCommandHandler;
use App\Event\Filter\ListFilterObject;
use App\Form\CommitteeCommandType;
use App\Form\CommitteeMemberFilterType;
use App\Form\EventCommandType;
use App\Repository\AdherentRepository;
use App\Repository\CommitteeMembershipRepository;
use App\Serializer\XlsxEncoder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/comites/{slug}")
 * @Security("is_granted('HOST_COMMITTEE', committee)")
 */
class CommitteeManagerController extends Controller
{
    private $manager;

    public function __construct(CommitteeManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route("/editer", name="app_committee_manager_edit", methods={"GET", "POST"})
     */
    public function editAction(
        Request $request,
        Committee $committee,
        CommitteeUpdateCommandHandler $commandHandler
    ): Response {
        $command = CommitteeCommand::createFromCommittee($committee);
        $form = $this->createForm(CommitteeCommandType::class, $command, [
            'with_social_networks' => true,
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

    /**
     * @Route("/evenements/ajouter", name="app_committee_manager_add_event", methods={"GET", "POST"})
     *
     * @Security("committee.isApproved()")
     */
    public function addEventAction(
        Request $request,
        Committee $committee,
        GeoCoder $geoCoder,
        EventCommandHandler $eventCommandHandler,
        EventRegistrationCommandHandler $eventRegistrationCommandHandler
    ): Response {
        $command = new EventCommand($this->getUser(), $committee);
        $command->setTimeZone($geoCoder->getTimezoneFromIp($request->getClientIp()));

        $form = $this
            ->createForm(EventCommandType::class, $command)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $eventCommandHandler->handle($command);
            $eventRegistrationCommandHandler->handle(new EventRegistrationCommand($event, $this->getUser()));

            $this->addFlash('info', 'committee.event.creation.success');

            return $this->redirectToRoute('app_event_show', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('committee_manager/add_event.html.twig', [
            'committee' => $committee,
            'committee_hosts' => $this->manager->getCommitteeHosts($committee),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/membres", name="app_committee_manager_list_members", methods={"GET"})
     *
     * @Security("committee.isApproved()")
     */
    public function listMembersAction(
        UserInterface $adherent,
        Request $request,
        Committee $committee,
        CommitteeMembershipRepository $repository,
        AdherentRepository $adherentRepository,
        SerializerInterface $serializer
    ): Response {
        /** @var Adherent $adherent */
        $form = $this
            ->createForm(CommitteeMemberFilterType::class, $filter = new ListFilterObject(), [
                'method' => 'GET',
                'csrf_protection' => false,
                'is_supervisor' => $adherent->isSupervisorOf($committee),
            ])
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('info', 'Le formulaire est invalide');

            return $this->redirectToRoute('app_committee_manager_list_members', ['slug' => $committee->getSlug()]);
        }

        if ($request->query->has('export')) {
            return new Response(
                $serializer->serialize(
                    $repository->getCommitteeMembershipsPaginator(
                        $committee,
                        $filter,
                        $request->query->getInt('page', 1),
                        null // without limit
                    ),
                    XlsxEncoder::FORMAT,
                    [
                        'groups' => ['export'],
                        DateTimeNormalizer::FORMAT_KEY => 'd/m/Y',
                        XlsxEncoder::HEADERS_KEY => [
                            'adherent.first_name' => 'Prénom',
                            'adherent.last_name_initial' => 'Nom',
                            'adherent.age' => 'Age',
                            'adherent.postal_code' => 'Code postal',
                            'adherent.city_name' => 'Ville',
                            'adherent.registered_at' => "Date d'adhesion",
                            'subscriptionDate' => 'A rejoint le comité le',
                        ],
                    ]
                ),
                Response::HTTP_OK,
                [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment;filename="membres-du-comite.xlsx"',
                    'Cache-Control' => 'max-age=0',
                ]
            );
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

    /**
     * @Route("/promouvoir-suppleant/{member_uuid}", name="app_committee_promote_host", methods={"GET", "POST"})
     * @Security("is_granted('SUPERVISE_COMMITTEE', committee)")
     * @Entity("member", expr="repository.findByUuid(member_uuid)")
     */
    public function promoteHostAction(Request $request, Committee $committee, Adherent $member): Response
    {
        if (!$this->manager->isPromotableHost($member, $committee)) {
            throw $this->createNotFoundException(sprintf('Member "%s" of committee "%s" can not be promoted as a host privileged person.', $member->getUuid(), $committee->getUuid()));
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->promote($member, $committee);
            $this->addFlash('info', 'committee.promote_host.success');

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

    /**
     * @Route("/retirer-suppleant/{member_uuid}", name="app_committee_demote_host", methods={"GET", "POST"})
     * @Security("is_granted('SUPERVISE_COMMITTEE', committee)")
     * @Entity("member", expr="repository.findByUuid(member_uuid)")
     */
    public function demoteHostAction(Request $request, Committee $committee, Adherent $member): Response
    {
        if (!$this->manager->isDemotableHost($member, $committee)) {
            throw $this->createNotFoundException(sprintf('Member "%s" of committee "%s" can not be demoted as a simple follower.', $member->getUuid(), $committee->getUuid()));
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

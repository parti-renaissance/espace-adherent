<?php

namespace AppBundle\Controller\EnMarche;

use AppBundle\Address\GeoCoder;
use AppBundle\Committee\CommitteeCommand;
use AppBundle\Committee\CommitteeContactMembersCommand;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Committee;
use AppBundle\Event\EventCommand;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Form\CommitteeCommandType;
use AppBundle\Form\ContactMembersType;
use AppBundle\Form\EventCommandType;
use AppBundle\Serializer\XlsxEncoder;
use AppBundle\Utils\GroupUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/comites/{slug}")
 * @Security("is_granted('HOST_COMMITTEE', committee)")
 */
class CommitteeManagerController extends Controller
{
    /**
     * @Route("/editer", name="app_committee_manager_edit")
     * @Method("GET|POST")
     */
    public function editAction(Request $request, Committee $committee): Response
    {
        $command = CommitteeCommand::createFromCommittee($committee);
        $form = $this->createForm(CommitteeCommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.committee.update_handler')->handle($command);
            $this->addFlash('info', 'committee.update.success');

            return $this->redirectToRoute('app_committee_manager_edit', [
                'slug' => $committee->getSlug(),
            ]);
        }

        return $this->render('committee_manager/edit.html.twig', [
            'form' => $form->createView(),
            'committee' => $committee,
            'committee_hosts' => $this->get('app.committee.manager')->getCommitteeHosts($committee),
        ]);
    }

    /**
     * @Route("/evenements/ajouter", name="app_committee_manager_add_event")
     * @Method("GET|POST")
     */
    public function addEventAction(Request $request, Committee $committee, GeoCoder $geoCoder): Response
    {
        $command = new EventCommand($this->getUser(), $committee);
        $command->setTimeZone($geoCoder->getTimezoneFromIp($request->getClientIp()));
        $form = $this->createForm(EventCommandType::class, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $this->get('app.event.handler')->handle($command);

            $registrationCommand = new EventRegistrationCommand($event, $this->getUser());
            $this->get('app.event.registration_handler')->handle($registrationCommand);

            $this->addFlash('info', 'committee.event.creation.success');

            return $this->redirectToRoute('app_event_show', [
                'slug' => $event->getSlug(),
            ]);
        }

        return $this->render('committee_manager/add_event.html.twig', [
            'committee' => $committee,
            'committee_hosts' => $this->get('app.committee.manager')->getCommitteeHosts($committee),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/membres", name="app_committee_manager_list_members")
     * @Method("GET")
     */
    public function listMembersAction(Committee $committee): Response
    {
        $committeeManager = $this->get('app.committee.manager');

        return $this->render('committee_manager/list_members.html.twig', [
            'committee' => $committee,
            'committee_hosts' => $committeeManager->getCommitteeHosts($committee),
            'members' => $committeeManager->getCommitteeMemberships($committee),
        ]);
    }

    /**
     * @Route("/membres/export", name="app_committee_manager_export_members")
     * @Method("POST")
     */
    public function exportMembersAction(
        Request $request,
        Committee $committee,
        SerializerInterface $serializer
    ): Response {
        if (!$this->isCsrfTokenValid('committee.export_members', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to export members.');
        }

        $committeeManager = $this->get('app.committee.manager');

        $uuids = GroupUtils::getUuidsFromJson($request->request->get('exports', ''));
        $adherents = GroupUtils::removeUnknownAdherents($uuids, $committeeManager->getCommitteeMembers($committee));

        return new Response(
            $serializer->serialize(
                $adherents,
                XlsxEncoder::FORMAT,
                [
                    'groups' => ['export'],
                    DateTimeNormalizer::FORMAT_KEY => 'd/m/Y',
                    XlsxEncoder::HEADERS_KEY => [
                        'first_name' => 'Prénom',
                        'last_name_initial' => 'Nom',
                        'age' => 'Age',
                        'postal_code' => 'Code postal',
                        'city_name' => 'Ville',
                        'registered_at' => "Date d'adhesion",
                    ],
                ]
            ), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment;filename="membres-du-comite.xlsx"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * @Route("/membres/contact", name="app_committee_contact_members")
     * @Method("POST")
     */
    public function contactMembersAction(Request $request, Committee $committee): Response
    {
        if (!$this->isCsrfTokenValid('committee.contact_members', $request->request->get('token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF protection token to contact members.');
        }

        $committeeManager = $this->get('app.committee.manager');

        $uuids = GroupUtils::getUuidsFromJson($request->request->get('contacts', ''));
        $adherents = GroupUtils::removeUnknownAdherents($uuids, $committeeManager->getCommitteeMembers($committee));
        $command = new CommitteeContactMembersCommand($adherents, $this->getUser());

        $contacts = GroupUtils::getUuidsFromAdherents($adherents);

        if (empty($contacts)) {
            $this->addFlash('info', 'committee.contact_members.none');

            return $this->redirectToRoute('app_committee_manager_list_members', [
                'slug' => $committee->getSlug(),
            ]);
        }

        $form = $this->createForm(ContactMembersType::class, $command)
            ->add('submit', SubmitType::class)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('app.committee.contact_members_handler')->handle($command);
            $this->addFlash('info', 'committee.contact_members.success');

            return $this->redirectToRoute('app_committee_manager_list_members', [
                'slug' => $committee->getSlug(),
            ]);
        }

        return $this->render('committee/contact.html.twig', [
            'committee' => $committee,
            'committee_hosts' => $committeeManager->getCommitteeHosts($committee),
            'contacts' => GroupUtils::getUuidsFromAdherents($adherents),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/promouvoir-suppleant/{member_uuid}", name="app_committee_promote_host")
     * @Method("GET|POST")
     * @Security("is_granted('SUPERVISE_COMMITTEE', committee)")
     * @Entity("member", expr="repository.findByUuid(member_uuid)")
     */
    public function promoteHostAction(Request $request, Committee $committee, Adherent $member): Response
    {
        $committeeManager = $this->get('app.committee.manager');
        if (!$committeeManager->isPromotableHost($member, $committee)) {
            throw $this->createNotFoundException(sprintf(
                'Member "%s" of committee "%s" can not be promoted as a host privileged person.',
                $member->getUuid(),
                $committee->getUuid()
            ));
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $committeeManager->promote($member, $committee);
            $this->addFlash('info', 'committee.promote_host.success');

            return $this->redirectToRoute('app_committee_manager_list_members', [
                'slug' => $committee->getSlug(),
            ]);
        }

        return $this->render('committee_manager/promote_host.html.twig', [
            'member' => $member,
            'committee' => $committee,
            'committee_hosts' => $committeeManager->getCommitteeHosts($committee),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/retirer-suppleant/{member_uuid}", name="app_committee_demote_host")
     * @Method("GET|POST")
     * @Security("is_granted('SUPERVISE_COMMITTEE', committee)")
     * @Entity("member", expr="repository.findByUuid(member_uuid)")
     */
    public function demoteHostAction(Request $request, Committee $committee, Adherent $member): Response
    {
        $committeeManager = $this->get('app.committee.manager');
        if (!$committeeManager->isDemotableHost($member, $committee)) {
            throw $this->createNotFoundException(sprintf(
                'Member "%s" of committee "%s" can not be demoted as a simple follower.',
                $member->getUuid(),
                $committee->getUuid()
            ));
        }

        $form = $this->createForm(FormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $committeeManager->demote($member, $committee);
            $this->addFlash('info', 'committee.demote_host.success');

            return $this->redirectToRoute('app_committee_manager_list_members', [
                'slug' => $committee->getSlug(),
            ]);
        }

        return $this->render('committee_manager/demote_host.html.twig', [
            'member' => $member,
            'committee' => $committee,
            'committee_hosts' => $committeeManager->getCommitteeHosts($committee),
            'form' => $form->createView(),
        ]);
    }
}

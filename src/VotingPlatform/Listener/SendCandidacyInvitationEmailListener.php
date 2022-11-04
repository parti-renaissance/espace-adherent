<?php

namespace App\VotingPlatform\Listener;

use App\Entity\CommitteeMembership;
use App\Mailer\MailerService;
use App\Mailer\Message\VotingPlatformCandidacyInvitationAcceptedMessage;
use App\Mailer\Message\VotingPlatformCandidacyInvitationCreatedMessage;
use App\Mailer\Message\VotingPlatformCandidacyInvitationDeclinedMessage;
use App\Mailer\Message\VotingPlatformCandidacyInvitationRemovedMessage;
use App\VotingPlatform\Designation\DesignationTypeEnum;
use App\VotingPlatform\Event\CandidacyInvitationEvent;
use App\VotingPlatform\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SendCandidacyInvitationEmailListener implements EventSubscriberInterface
{
    private $mailer;
    private $urlGenerator;
    private $translator;

    public function __construct(
        MailerService $transactionalMailer,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->mailer = $transactionalMailer;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Events::CANDIDACY_INVITATION_UPDATE => 'onInvitationUpdate',
            Events::CANDIDACY_INVITATION_DECLINE => 'onInvitationDecline',
            Events::CANDIDACY_INVITATION_ACCEPT => 'onInvitationAccept',
            Events::CANDIDACY_INVITATION_REMOVE => 'onInvitationRemove',
        ];
    }

    public function onInvitationUpdate(CandidacyInvitationEvent $event): void
    {
        $candidacy = $event->getCandidacy();
        $invitations = $event->getInvitations();
        $previouslyInvitedMemberships = $event->getPreviouslyInvitedMemberships();
        $designation = $candidacy->getElection()->getDesignation();

        $invitedMemberships = [];

        foreach ($invitations as $invitation) {
            $invitedMemberships[] = $invitedMembership = $invitation->getMembership();

            if ($invitation && (!$previouslyInvitedMemberships || !\in_array($invitation->getMembership(), $previouslyInvitedMemberships))) {
                if ($designation->isCommitteeType()) {
                    /** @var CommitteeMembership $invitedMembership */
                    $committee = $invitedMembership->getCommittee();
                    $url = $this->urlGenerator->generate('app_committee_candidature_invitation_list', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
                    $params = ['committee_name' => $committee->getName()];
                } else {
                    $url = $this->urlGenerator->generate('app_territorial_council_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
                    $params = ['quality' => DesignationTypeEnum::COPOL === $designation->getType() ? $this->translator->trans('territorial_council.membership.quality.'.$candidacy->getQuality()) : null];
                }

                $this->mailer->sendMessage(
                    VotingPlatformCandidacyInvitationCreatedMessage::create(
                        $invitedMembership->getAdherent(),
                        $candidacy->getMembership()->getAdherent(),
                        $designation,
                        $url,
                        $params
                    )
                );
            }
        }

        foreach ($previouslyInvitedMemberships as $invitedMembership) {
            if (!$invitations || !\in_array($invitedMembership, $invitedMemberships)) {
                if ($designation->isCommitteeType()) {
                    $url = $this->urlGenerator->generate('app_committee_show', ['slug' => $invitedMembership->getCommittee()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
                } else {
                    $url = $this->urlGenerator->generate('app_territorial_council_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
                }

                $this->mailer->sendMessage(VotingPlatformCandidacyInvitationRemovedMessage::create(
                    $invitedMembership->getAdherent(),
                    $candidacy->getMembership()->getAdherent(),
                    $candidacy->getElection()->getDesignation(),
                    $url
                ));
            }
        }
    }

    public function onInvitationRemove(CandidacyInvitationEvent $event): void
    {
        foreach ($event->getInvitations() as $invitation) {
            $candidacy = $invitation->getCandidacy();
            $invitedMembership = $invitation->getMembership();
            $designation = $candidacy->getElection()->getDesignation();

            if ($designation->isCommitteeType()) {
                $url = $this->urlGenerator->generate('app_committee_show', ['slug' => $invitedMembership->getCommittee()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
            } else {
                $url = $this->urlGenerator->generate('app_territorial_council_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
            }

            $this->mailer->sendMessage(VotingPlatformCandidacyInvitationRemovedMessage::create(
                $invitedMembership->getAdherent(),
                $candidacy->getMembership()->getAdherent(),
                $designation,
                $url
            ));
        }
    }

    public function onInvitationDecline(CandidacyInvitationEvent $event): void
    {
        $candidacy = $event->getCandidacy();
        $invitation = current($event->getInvitations());

        $invitedMembership = $invitation->getMembership();
        $designation = $candidacy->getElection()->getDesignation();

        if ($designation->isCommitteeType()) {
            /** @var CommitteeMembership $invitedMembership */
            $url = $this->urlGenerator->generate('app_committee_candidature_select_pair_candidate', ['slug' => $invitedMembership->getCommittee()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
        } else {
            $url = $this->urlGenerator->generate('app_territorial_council_candidature_select_pair_candidate', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $this->mailer->sendMessage(VotingPlatformCandidacyInvitationDeclinedMessage::create(
            $invitation->getMembership()->getAdherent(),
            $candidacy->getMembership()->getAdherent(),
            $candidacy->getElection()->getDesignation(),
            $url
        ));
    }

    public function onInvitationAccept(CandidacyInvitationEvent $event): void
    {
        $candidacy = $event->getCandidacy();
        $invitedCandidacy = $event->getInvitedCandidacy();

        $invitedMembership = $invitedCandidacy->getMembership();
        $designation = $candidacy->getElection()->getDesignation();

        if ($designation->isCommitteeType()) {
            /** @var CommitteeMembership $invitedMembership */
            $committee = $invitedMembership->getCommittee();
            $url = $this->urlGenerator->generate('app_committee_candidature_candidacy_list', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
            $params = ['committee_name' => $committee->getName()];
        } else {
            $url = $this->urlGenerator->generate('app_territorial_council_candidacy_list', [], UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $this->mailer->sendMessage(VotingPlatformCandidacyInvitationAcceptedMessage::create(
            $event->getInvitedCandidacy(),
            $candidacy,
            $designation,
            $url,
            $params ?? []
        ));
    }
}

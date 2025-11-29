<?php

declare(strict_types=1);

namespace App\VotingPlatform\Listener;

use App\Entity\CommitteeMembership;
use App\Entity\VotingPlatform\Designation\Designation;
use App\Mailer\MailerService;
use App\Mailer\Message\VotingPlatformCandidacyInvitationAcceptedMessage;
use App\Mailer\Message\VotingPlatformCandidacyInvitationCreatedMessage;
use App\Mailer\Message\VotingPlatformCandidacyInvitationDeclinedMessage;
use App\Mailer\Message\VotingPlatformCandidacyInvitationRemovedMessage;
use App\VotingPlatform\Event\CandidacyInvitationEvent;
use App\VotingPlatform\Events;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SendCandidacyInvitationEmailListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly MailerService $transactionalMailer,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
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

        if (!$designation->isCommitteeTypes()) {
            return;
        }

        $invitedMemberships = [];

        foreach ($invitations as $invitation) {
            $invitedMemberships[] = $invitedMembership = $invitation->getMembership();

            if ($invitation && (!$previouslyInvitedMemberships || !\in_array($invitation->getMembership(), $previouslyInvitedMemberships))) {
                /** @var CommitteeMembership $invitedMembership */
                $committee = $invitedMembership->getCommittee();
                $url = $this->urlGenerator->generate('app_committee_candidature_invitation_list', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
                $params = ['committee_name' => $committee->getName()];

                $this->transactionalMailer->sendMessage(
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
                $url = $this->urlGenerator->generate('app_committee_show', ['slug' => $invitedMembership->getCommittee()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);

                $this->transactionalMailer->sendMessage(VotingPlatformCandidacyInvitationRemovedMessage::create(
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
            /** @var Designation $designation */
            $designation = $candidacy->getElection()->getDesignation();

            if ($designation->isCommitteeTypes()) {
                $url = $this->urlGenerator->generate('app_committee_show', ['slug' => $invitedMembership->getCommittee()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);

                $this->transactionalMailer->sendMessage(VotingPlatformCandidacyInvitationRemovedMessage::create(
                    $invitedMembership->getAdherent(),
                    $candidacy->getMembership()->getAdherent(),
                    $designation,
                    $url
                ));
            }
        }
    }

    public function onInvitationDecline(CandidacyInvitationEvent $event): void
    {
        $candidacy = $event->getCandidacy();
        $invitation = current($event->getInvitations());

        $invitedMembership = $invitation->getMembership();
        $designation = $candidacy->getElection()->getDesignation();

        if (!$designation->isCommitteeTypes()) {
            return;
        }

        /** @var CommitteeMembership $invitedMembership */
        $url = $this->urlGenerator->generate('app_committee_candidature_select_pair_candidate', ['slug' => $invitedMembership->getCommittee()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);

        $this->transactionalMailer->sendMessage(VotingPlatformCandidacyInvitationDeclinedMessage::create(
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

        if (!$designation->isCommitteeTypes()) {
            return;
        }

        /** @var CommitteeMembership $invitedMembership */
        $committee = $invitedMembership->getCommittee();
        $url = $this->urlGenerator->generate('app_committee_candidature_candidacy_list', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
        $params = ['committee_name' => $committee->getName()];

        $this->transactionalMailer->sendMessage(VotingPlatformCandidacyInvitationAcceptedMessage::create(
            $event->getInvitedCandidacy(),
            $candidacy,
            $designation,
            $url,
            $params
        ));
    }
}

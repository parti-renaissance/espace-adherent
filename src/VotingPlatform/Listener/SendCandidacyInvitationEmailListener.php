<?php

namespace App\VotingPlatform\Listener;

use App\Entity\CommitteeMembership;
use App\Mailer\MailerService;
use App\Mailer\Message\VotingPlatformCandidacyInvitationAcceptedMessage;
use App\Mailer\Message\VotingPlatformCandidacyInvitationCreatedMessage;
use App\Mailer\Message\VotingPlatformCandidacyInvitationDeclinedMessage;
use App\Mailer\Message\VotingPlatformCandidacyInvitationRemovedMessage;
use App\TerritorialCouncil\Events;
use App\VotingPlatform\Event\CandidacyInvitationEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

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

    public static function getSubscribedEvents()
    {
        return [
            Events::CANDIDACY_INVITATION_UPDATE => 'onInvitationUpdate',
            Events::CANDIDACY_INVITATION_DECLINE => 'onInvitationDecline',
            Events::CANDIDACY_INVITATION_ACCEPT => 'onInvitationAccept',
        ];
    }

    public function onInvitationUpdate(CandidacyInvitationEvent $event): void
    {
        $candidacy = $event->getCandidacy();
        $invitation = $event->getInvitation();
        $previouslyInvitedMembership = $event->getPreviouslyInvitedMembership();

        $invitedMembership = $invitation->getMembership();
        $designation = $candidacy->getElection()->getDesignation();

        if ($invitation && (!$previouslyInvitedMembership || $previouslyInvitedMembership !== $invitation->getMembership())) {
            if ($designation->isCommitteeType()) {
                /** @var CommitteeMembership $invitedMembership */
                $committee = $invitedMembership->getCommittee();
                $url = $this->urlGenerator->generate('app_committee_candidature_invitation_list', ['slug' => $committee->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
                $params = ['committee_name' => $committee->getName()];
            } else {
                $url = $this->urlGenerator->generate('app_territorial_council_candidature_invitation_list', [], UrlGeneratorInterface::ABSOLUTE_URL);
                $params = ['quality' => $this->translator->trans('territorial_council.membership.quality.'.$candidacy->getQuality())];
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

        if ($previouslyInvitedMembership && (!$invitation || $previouslyInvitedMembership !== $invitation->getMembership())) {
            if ($designation->isCommitteeType()) {
                $url = $this->urlGenerator->generate('app_committee_show', ['slug' => $invitedMembership->getCommittee()->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);
            } else {
                $url = $this->urlGenerator->generate('app_territorial_council_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
            }

            $this->mailer->sendMessage(VotingPlatformCandidacyInvitationRemovedMessage::create(
                $previouslyInvitedMembership->getAdherent(),
                $candidacy->getMembership()->getAdherent(),
                $candidacy->getElection()->getDesignation(),
                $url
            ));
        }
    }

    public function onInvitationDecline(CandidacyInvitationEvent $event): void
    {
        $candidacy = $event->getCandidacy();
        $invitation = $event->getInvitation();

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
        $invitation = $event->getInvitation();

        $invitedMembership = $invitation->getMembership();
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
            $invitation->getMembership()->getAdherent(),
            $candidacy->getMembership()->getAdherent(),
            $candidacy->getElection()->getDesignation(),
            $url,
            $params ?? []
        ));
    }
}

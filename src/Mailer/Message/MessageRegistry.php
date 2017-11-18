<?php

namespace AppBundle\Mailer\Message;

class MessageRegistry
{
    private $transactionalTypes = [
        AdherentAccountActivationMessage::class => 'adherent_account_activation',
        AdherentAccountConfirmationMessage::class => 'adherent_account_confirmation',
        AdherentResetPasswordConfirmationMessage::class => 'adherent_reset_password_confirmation',
        AdherentResetPasswordMessage::class => 'adherent_reset_password',
        AdherentTerminateMembershipMessage::class => 'adherent_terminate_membership',
        BoardMemberMessage::class => 'board_member',
        CitizenInitiativeActivitySubscriptionMessage::class => 'citizen_initiative_activity_subscription',
        CitizenInitiativeAdherentsNearMessage::class => 'citizen_initiative_adherents_near',
        CitizenInitiativeCreationConfirmationMessage::class => 'citizen_initiative_creation_confirmation',
        CitizenInitiativeInvitationMessage::class => 'citizen_initiative_invitation',
        CitizenInitiativeNearSupervisorsMessage::class => 'citizen_initiative_near_supervisors',
        CitizenInitiativeOrganizerValidationMessage::class => 'citizen_initiative_organizer_validation',
        CitizenInitiativeRegistrationConfirmationMessage::class => 'citizen_initiative_registration_confirmation',
        CommitteeApprovalConfirmationMessage::class => 'committee_approval_confirmation',
        CommitteeApprovalReferentMessage::class => 'committee_approval_referent',
        CommitteeCreationConfirmationMessage::class => 'committee_creation_confirmation',
        CommitteeNewFollowerMessage::class => 'committee_new_follower',
        DonationMessage::class => 'donation',
        EventCancellationMessage::class => 'event_cancellation',
        EventInvitationMessage::class => 'event_invitation',
        EventNotificationMessage::class => 'event_notification',
        EventRegistrationConfirmationMessage::class => 'event_registration_confirmation',
        JeMarcheReportMessage::class => 'je_marche_report',
        LegislativeCampaignContactMessage::class => 'legislative_campaign_contact',
        NewsletterInvitationMessage::class => 'newsletter_invitation',
        NewsletterSubscriptionMessage::class => 'newsletter_subscription',
        ProcurationProxyCancelledMessage::class => 'procuration_proxy_cancelled',
        ProcurationProxyFoundMessage::class => 'procuration_proxy_found',
        ProcurationProxyReminderMessage::class => 'procuration_proxy_reminder',
        PurchasingPowerMessage::class => 'purchasing_power_message',
    ];

    private $campaignTypes = [
        AdherentContactMessage::class => 'adherent_contact',
        CommitteeCitizenInitiativeNotificationMessage::class => 'committee_citizen_initiative_notification',
        CommitteeCitizenInitiativeOrganizerNotificationMessage::class => 'committee_citizen_initiative_organizer_notification',
        CommitteeContactMembersMessage::class => 'committee_contact_members',
        CommitteeMessageNotificationMessage::class => 'committee_message_notification',
        EventContactMembersMessage::class => 'event_contact_members',
        InvitationMessage::class => 'invitation',
        ReferentMessage::class => 'referent',
        TonMacronFriendMessage::class => 'ton_macron_friend',
    ];

    public function getTypes(): array
    {
        return array_merge($this->transactionalTypes, $this->campaignTypes);
    }

    public function getTemplate(string $type): string
    {
        $types = $this->getTypes();

        if (!array_key_exists($type, $types)) {
            throw new \InvalidArgumentException('Message type "%s" is not registered.');
        }

        return $types[$type];
    }

    public function getTypeByTemplate(string $template): string
    {
        if (false === $key = array_search($template, $this->getTypes(), true)) {
            throw new \InvalidArgumentException('No type found for template "%s", $template.');
        }

        return $key;
    }
}

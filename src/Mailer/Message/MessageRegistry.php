<?php

namespace AppBundle\Mailer\Message;

class MessageRegistry
{
    private $transactional = [
        AdherentAccountActivationMessage::class => 'adherent_account_activation_message',
        AdherentAccountConfirmationMessage::class => 'adherent_account_confirmation_message',
        AdherentResetPasswordConfirmationMessage::class => 'adherent_reset_password_confirmation_message',
        AdherentResetPasswordMessage::class => 'adherent_reset_password_message',
        AdherentTerminateMembershipMessage::class => 'adherent_terminate_membership_message',
        BoardMemberMessage::class => 'board_member_message',
        CitizenActionCancellationMessage::class => 'citizen_action_cancellation_message',
        CitizenActionRegistrationConfirmationMessage::class => 'citizen_action_registration_confirmation_message',
        CitizenProjectApprovalConfirmationMessage::class => 'citizen_project_approval_confirmation_message',
        CitizenProjectCommentMessage::class => 'citizen_project_comment_message',
        CitizenProjectCreationConfirmationMessage::class => 'citizen_project_creation_confirmation_message',
        CitizenProjectCreationCoordinatorNotificationMessage::class => 'citizen_project_creation_coordinator_notification_message',
        CitizenProjectCreationNotificationMessage::class => 'citizen_project_creation_notification_message',
        CitizenProjectNewFollowerMessage::class => 'citizen_project_new_follower_message',
        CitizenProjectRequestCommitteeSupportMessage::class => 'citizen_project_request_committee_support_message',
        CommitteeApprovalConfirmationMessage::class => 'committee_approval_confirmation_message',
        CommitteeApprovalReferentMessage::class => 'committee_approval_referent_message',
        CommitteeCreationConfirmationMessage::class => 'committee_creation_confirmation_message',
        CommitteeNewFollowerMessage::class => 'committee_new_follower_message',
        DonationMessage::class => 'donation_message',
        EventCancellationMessage::class => 'event_cancellation_message',
        EventInvitationMessage::class => 'event_invitation_message',
        EventNotificationMessage::class => 'event_notification_message',
        EventRegistrationConfirmationMessage::class => 'event_registration_confirmation_message',
        JeMarcheReportMessage::class => 'je_marche_report_message',
        LegislativeCampaignContactMessage::class => 'legislative_campaign_contact_message',
        NewsletterInvitationMessage::class => 'newsletter_invitation_message',
        NewsletterSubscriptionMessage::class => 'newsletter_subscription_message',
        ProcurationProxyCancelledMessage::class => 'procuration_proxy_cancelled_message',
        ProcurationProxyFoundMessage::class => 'procuration_proxy_found_message',
        ProcurationProxyReminderMessage::class => 'procuration_proxy_reminder_message',
        PurchasingPowerMessage::class => 'purchasing_power_message',
    ];

    private $campaign = [
        AdherentContactMessage::class => 'adherent_contact_message',
        CitizenActionContactParticipantsMessage::class => 'citizen_action_contact_participants_message',
        CitizenProjectContactActorsMessage::class => 'citizen_project_contact_actors_message',
        CommitteeContactMembersMessage::class => 'committee_contact_members_message',
        CommitteeMessageNotificationMessage::class => 'committee_message_notification_message',
        EventContactMembersMessage::class => 'event_contact_members_message',
        InvitationMessage::class => 'invitation_message',
        ReferentMessage::class => 'referent_message',
        TonMacronFriendMessage::class => 'ton_macron_friend_message',
    ];

    public function getTemplateName(string $messageClass): string
    {
        if (\array_key_exists($messageClass, $this->transactional)) {
            return $this->transactional[$messageClass];
        }

        if (\array_key_exists($messageClass, $this->campaign)) {
            return $this->campaign[$messageClass];
        }

        throw new \Exception("Message $messageClass does not exist.");
    }

    public function getMessageTemplate(Message $message): string
    {
        return $this->getTemplateName(get_class($message));
    }

    public function getMessageClass(string $name): string
    {
        if (\in_array($name, $this->transactional)) {
            return \array_search($name, $this->transactional);
        }

        if (\in_array($name, $this->campaign)) {
            return \array_search($name, $this->campaign);
        }

        throw new \Exception("Message with name $name does not exist.");
    }

    public function getAllMessages(): array
    {
        return \array_merge($this->transactional, $this->campaign);
    }
}

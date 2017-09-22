<?php

namespace AppBundle\DataFixtures\ORM;

use AppBundle\Entity\MailjetTemplate;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadMailjetTemplatesData implements FixtureInterface
{
    const MAILJET_TEMPLATES = [
        'CitizenInitiativeOrganizerValidationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'AdherentResetPasswordMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'ProcurationProxyFoundMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'NewsletterInvitationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'CitizenInitiativeActivitySubscriptionMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'EventRegistrationConfirmationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'CitizenInitiativeCreationConfirmationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'ProcurationProxyCancelledMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'CitizenInitiativeNearSupervisorsMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'JeMarcheReportMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'CitizenInitiativeInvitationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'CommitteeContactMembersMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'CitizenInitiativeAdherentsNearMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'InvitationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'ProcurationProxyReminderMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'CommitteeCreationConfirmationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'CommitteeMessageNotificationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'EventCancellationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'AdherentAccountActivationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'EventInvitationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'EventContactMembersMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'AdherentTerminateMembershipMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'CommitteeCitizenInitiativeNotificationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'CommitteeApprovalConfirmationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'AdherentResetPasswordConfirmationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'TonMacronFriendMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'NewsletterSubscriptionMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'CommitteeNewFollowerMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'CitizenInitiativeRegistrationConfirmationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'CommitteeCitizenInitiativeOrganizerNotificationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'EventNotificationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'AdherentContactMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'DonationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'LegislativeCampaignContactMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'ReferentMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
        'AdherentAccountConfirmationMessage' => [
            'senderEmail' => 'contact@en-marche.fr',
            'senderName' => 'En Marche !',
        ],
    ];

    public function load(ObjectManager $manager)
    {
        foreach (self::MAILJET_TEMPLATES as $messageClass => $data) {
            $template = new MailjetTemplate(
                Uuid::uuid4(),
                $messageClass,
                $data['senderEmail'],
                $data['senderName']
            );

            $manager->persist($template);
        }

        $manager->flush();
    }
}

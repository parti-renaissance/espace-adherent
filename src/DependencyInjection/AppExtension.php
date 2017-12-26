<?php

namespace AppBundle\DependencyInjection;

use AppBundle\Address\Address;
use AppBundle\Committee\CommitteeCommand;
use AppBundle\Committee\CommitteeContactMembersCommand;
use AppBundle\Committee\CommitteeCreationCommand;
use AppBundle\Committee\Feed\CommitteeMessage;
use AppBundle\Contact\ContactMessage;
use AppBundle\Donation\DonationRequest;
use AppBundle\Event\EventCommand;
use AppBundle\Event\EventContactMembersCommand;
use AppBundle\Event\EventInvitation;
use AppBundle\Event\EventRegistrationCommand;
use AppBundle\Legislative\LegislativeCampaignContactMessage;
use AppBundle\Membership\MembershipRequest;
use AppBundle\Newsletter\Invitation;
use AppBundle\Referent\ReferentMessage;
use AppBundle\TonMacron\InvitationProcessor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AppExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->addAnnotatedClassesToCompile([
            Address::class,
            CommitteeCommand::class,
            CommitteeMessage::class,
            CommitteeContactMembersCommand::class,
            CommitteeCreationCommand::class,
            ContactMessage::class,
            DonationRequest::class,
            EventCommand::class,
            EventContactMembersCommand::class,
            EventInvitation::class,
            EventRegistrationCommand::class,
            LegislativeCampaignContactMessage::class,
            MembershipRequest::class,
            Invitation::class,
            ReferentMessage::class,
            InvitationProcessor::class,

            'AppBundle\\Validator\\*',
        ]);
    }
}

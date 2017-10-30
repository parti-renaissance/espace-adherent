<?php

namespace AppBundle\Group;

use AppBundle\Entity\Group;
use AppBundle\Mailjet\MailjetService;
use AppBundle\Mailjet\Message\GroupApprovalConfirmationMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GroupManagementAuthority
{
    private $manager;
    private $mailjet;
    private $urlGenerator;

    public function __construct(
        GroupManager $manager,
        UrlGeneratorInterface $urlGenerator,
        MailjetService $mailjet
    ) {
        $this->manager = $manager;
        $this->urlGenerator = $urlGenerator;
        $this->mailjet = $mailjet;
    }

    public function approve(Group $group): void
    {
        $this->manager->approveGroup($group);

        $this->mailjet->sendMessage(GroupApprovalConfirmationMessage::create(
            $this->manager->getGroupCreator($group),
            $group->getCityName(),
            $this->urlGenerator->generate('app_group_show', ['slug' => $group->getSlug()])
        ));
    }

    public function refuse(Group $group): void
    {
        $this->manager->refuseGroup($group);
    }
}

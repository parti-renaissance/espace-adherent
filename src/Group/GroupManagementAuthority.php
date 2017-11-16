<?php

namespace AppBundle\Group;

use AppBundle\Entity\Group;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\GroupApprovalConfirmationMessage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GroupManagementAuthority
{
    private $manager;
    private $mailer;
    private $urlGenerator;

    public function __construct(
        GroupManager $manager,
        UrlGeneratorInterface $urlGenerator,
        MailerService $mailer
    ) {
        $this->manager = $manager;
        $this->urlGenerator = $urlGenerator;
        $this->mailer = $mailer;
    }

    public function approve(Group $group): void
    {
        $this->manager->approveGroup($group);

        $this->mailer->sendMessage(GroupApprovalConfirmationMessage::create(
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

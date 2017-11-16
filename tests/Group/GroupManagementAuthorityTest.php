<?php

namespace Tests\AppBundle\Group;

use AppBundle\DataFixtures\ORM\LoadGroupData;
use AppBundle\Group\GroupManagementAuthority;
use AppBundle\Group\GroupManager;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Group;
use AppBundle\Mailer\MailerService;
use AppBundle\Mailer\Message\GroupApprovalConfirmationMessage;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @functional
 */
class GroupManagementAuthorityTest extends TestCase
{
    public function testApprove()
    {
        $group = $this->createGroup(LoadGroupData::GROUP_1_UUID, 'Paris 8e');
        $administrator = $this->createAdministrator(LoadAdherentData::ADHERENT_3_UUID);

        $manager = $this->createManager($administrator);
        // ensure group is approved
        $manager->expects($this->once())->method('approveGroup')->with($group);

        $mailer = $this->createMock(MailerService::class);
        $mailer->expects($this->at(0))
            ->method('sendMessage')
            ->with($this->isInstanceOf(GroupApprovalConfirmationMessage::class));

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->expects($this->any())->method('generate')->willReturn(sprintf(
            '/groupes/%s',
            'mooc-paris'
        ));

        $groupManagementAuthority = new GroupManagementAuthority($manager, $urlGenerator, $mailer);
        $groupManagementAuthority->approve($group);
    }

    private function createGroup(string $uuid, string $cityName): Group
    {
        $groupUuid = Uuid::fromString($uuid);

        $group = $this->createMock(Group::class);
        $group->expects($this->any())->method('getUuid')->willReturn($groupUuid);
        $group->expects($this->any())->method('getCityName')->willReturn($cityName);

        return $group;
    }

    private function createAdministrator(string $uuid): Adherent
    {
        $administratorUuid = Uuid::fromString($uuid);

        $administrator = $this->createMock(Adherent::class);
        $administrator->expects($this->any())->method('getUuid')->willReturn($administratorUuid);

        return $administrator;
    }

    private function createManager(?Adherent $administrator = null): GroupManager
    {
        $manager = $this->createMock(GroupManager::class);

        if ($administrator) {
            $manager->expects($this->any())->method('getGroupCreator')->willReturn($administrator);
        }

        return $manager;
    }
}

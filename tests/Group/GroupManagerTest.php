<?php

namespace Tests\AppBundle\Group;

use AppBundle\Collection\AdherentCollection;
use AppBundle\DataFixtures\ORM\LoadGroupData;
use AppBundle\Group\GroupManager;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Group;
use AppBundle\Entity\GroupMembership;
use Ramsey\Uuid\Uuid;
use Tests\AppBundle\MysqlWebTestCase;
use Tests\AppBundle\TestHelperTrait;

/**
 * @group functional
 */
class GroupManagerTest extends MysqlWebTestCase
{
    use TestHelperTrait;

    /* @var GroupManager */
    private $groupManager;

    public function testGetGroupAdministrators()
    {
        $this->assertInstanceOf(
            AdherentCollection::class,
            $administrators = $this->groupManager->getGroupAdministrators($this->getGroupMock(LoadGroupData::GROUP_1_UUID))
        );

        // Approved groups
        $this->assertCount(2, $administrators);
        $this->assertCount(2, $this->groupManager->getGroupAdministrators($this->getGroupMock(LoadGroupData::GROUP_3_UUID)));
        $this->assertCount(1, $this->groupManager->getGroupAdministrators($this->getGroupMock(LoadGroupData::GROUP_4_UUID)));
        $this->assertCount(1, $this->groupManager->getGroupAdministrators($this->getGroupMock(LoadGroupData::GROUP_5_UUID)));

        // Unapproved groups
        $this->assertCount(1, $this->groupManager->getGroupAdministrators($this->getGroupMock(LoadGroupData::GROUP_2_UUID)));
    }

    public function testGetGroupFollowers()
    {
        $group = $this->getGroupMock(LoadGroupData::GROUP_1_UUID);
        $this->assertInstanceOf(AdherentCollection::class, $administrators = $this->groupManager->getGroupAdministrators($group));

        // Approved groups
        $this->assertCount(2, $administrators);
        $this->assertCount(2, $this->groupManager->getGroupFollowers($group));

        $group = $this->getGroupMock(LoadGroupData::GROUP_3_UUID);
        $this->assertCount(2, $this->groupManager->getGroupAdministrators($group));
        $this->assertCount(0, $this->groupManager->getGroupFollowers($group));

        $group = $this->getGroupMock(LoadGroupData::GROUP_4_UUID);
        $this->assertCount(1, $this->groupManager->getGroupAdministrators($group));
        $this->assertCount(1, $this->groupManager->getGroupFollowers($group));

        $group = $this->getGroupMock(LoadGroupData::GROUP_5_UUID);
        $this->assertCount(1, $this->groupManager->getGroupAdministrators($group));
        $this->assertCount(2, $this->groupManager->getGroupFollowers($group));

        // Unapproved groups
        $this->assertCount(1, $this->groupManager->getGroupAdministrators($this->getGroupMock(LoadGroupData::GROUP_2_UUID)));
    }

    public function testGetAdherentGroups()
    {
        $adherent = $this->getAdherentRepository()->findByUuid(LoadAdherentData::ADHERENT_3_UUID);

        // Without any fixed limit.
        $this->assertCount(8, $groups = $this->groupManager->getAdherentGroups($adherent));
        $this->assertSame('MOOC à Paris 8', (string) $groups[0]);
        $this->assertSame('Formation en ligne ouverte à tous à Évry', (string) $groups[1]);
        $this->assertSame('MOOC à New York City', (string) $groups[2]);
        $this->assertSame('MOOC à Dammarie-les-Lys', (string) $groups[3]);
        $this->assertSame('Massive Open Online Course', (string) $groups[4]);
        $this->assertSame('Formation en ligne ouverte à tous', (string) $groups[5]);
        $this->assertSame('Équipe MOOC à Berlin', (string) $groups[6]);
        $this->assertSame('En Marche - MOOC', (string) $groups[7]);
    }

    public function testChangePrivilegeNotDefinedPrivilege()
    {
        $adherent = $this->getAdherentRepository()->findByUuid(LoadAdherentData::ADHERENT_3_UUID);
        $group = $this->getGroupRepository()->findOneByUuid(LoadGroupData::GROUP_1_UUID);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid privilege WRONG_PRIVILEGE');

        $this->groupManager->changePrivilege($adherent, $group, 'WRONG_PRIVILEGE');
    }

    public function testChangePrivilege()
    {
        $adherent = $this->getAdherentRepository()->findByUuid(LoadAdherentData::ADHERENT_3_UUID);
        $adherent2 = $this->getAdherentRepository()->findByUuid(LoadAdherentData::ADHERENT_2_UUID);
        $group = $this->getGroupRepository()->findOneByUuid(LoadGroupData::GROUP_1_UUID);

        // Change privileges of the first member ADMINISTRATOR => FOLLOWER => ADMINISTRATOR
        $this->assertEquals(true, $adherent->getGroupMembershipFor($group)->isAdministrator());
        $this->assertEquals(false, $adherent->getGroupMembershipFor($group)->isFollower());

        $this->groupManager->changePrivilege($adherent, $group, GroupMembership::GROUP_FOLLOWER);

        $this->assertEquals(true, $adherent->getGroupMembershipFor($group)->isFollower());
        $this->assertEquals(false, $adherent->getGroupMembershipFor($group)->isAdministrator());

        $this->groupManager->changePrivilege($adherent, $group, GroupMembership::GROUP_ADMINISTRATOR);

        $this->assertEquals(true, $adherent->getGroupMembershipFor($group)->isAdministrator());
        $this->assertEquals(false, $adherent->getGroupMembershipFor($group)->isFollower());

        // Change privileges of the second member: FOLLOWER => ADMINISTRATOR
        $this->assertEquals(true, $adherent2->getGroupMembershipFor($group)->isFollower());
        $this->assertEquals(false, $adherent2->getGroupMembershipFor($group)->isAdministrator());

        $this->groupManager->changePrivilege($adherent2, $group, GroupMembership::GROUP_ADMINISTRATOR);

        $this->assertEquals(true, $adherent2->getGroupMembershipFor($group)->isAdministrator());
        $this->assertEquals(false, $adherent2->getGroupMembershipFor($group)->isFollower());
    }

    private function getGroupMock(string $uuid)
    {
        $mock = $this->createMock(Group::class);
        $mock
            ->expects($this->any())
            ->method('getUuid')
            ->willReturn(Uuid::fromString($uuid))
        ;

        return $mock;
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
            LoadGroupData::class,
        ]);

        $this->container = $this->getContainer();
        $this->groupManager = new GroupManager($this->getManagerRegistry());
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->container = null;
        $this->groupManager = null;

        parent::tearDown();
    }
}

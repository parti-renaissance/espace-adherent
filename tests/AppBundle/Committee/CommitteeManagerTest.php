<?php

namespace Tests\AppBundle\Committee;

use AppBundle\Committee\CommitteeManager;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\Committee;
use Ramsey\Uuid\Uuid;
use Tests\AppBundle\SqliteWebTestCase;
use Tests\AppBundle\TestHelperTrait;

class CommitteeManagerTest extends SqliteWebTestCase
{
    use TestHelperTrait;

    /* @var CommitteeManager */
    private $manager;

    public function testGetMembersCount()
    {
        $this->assertSame(4, $this->manager->getMembersCount($this->createCommitteeMock(LoadAdherentData::COMMITTEE_1_UUID)));
        $this->assertSame(0, $this->manager->getMembersCount($this->createCommitteeMock(LoadAdherentData::COMMITTEE_2_UUID)));
        $this->assertSame(0, $this->manager->getMembersCount($this->createCommitteeMock(LoadAdherentData::COMMITTEE_3_UUID)));
        $this->assertSame(1, $this->manager->getMembersCount($this->createCommitteeMock(LoadAdherentData::COMMITTEE_4_UUID)));
        $this->assertSame(1, $this->manager->getMembersCount($this->createCommitteeMock(LoadAdherentData::COMMITTEE_5_UUID)));
    }

    public function testFindCommitteeHostMembersList()
    {
        // Approved committees
        $this->assertCount(2, $this->manager->findCommitteeHostsList($this->createCommitteeMock(LoadAdherentData::COMMITTEE_1_UUID)));
        $this->assertCount(1, $this->manager->findCommitteeHostsList($this->createCommitteeMock(LoadAdherentData::COMMITTEE_4_UUID)));
        $this->assertCount(1, $this->manager->findCommitteeHostsList($this->createCommitteeMock(LoadAdherentData::COMMITTEE_5_UUID)));

        // Unapproved committees
        $this->assertCount(0, $this->manager->findCommitteeHostsList($this->createCommitteeMock(LoadAdherentData::COMMITTEE_2_UUID)));
        $this->assertCount(0, $this->manager->findCommitteeHostsList($this->createCommitteeMock(LoadAdherentData::COMMITTEE_3_UUID)));
    }

    private function createCommitteeMock(string $uuid)
    {
        $mock = $this
            ->getMockBuilder(Committee::class)
            ->disableOriginalConstructor()
            ->getMock();

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
        ]);

        $this->container = $this->getContainer();

        $this->manager = new CommitteeManager(
            $this->getAdherentRepository(),
            $this->getCommitteeMembershipRepository()
        );
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->manager = null;
        $this->container = null;

        parent::tearDown();
    }
}

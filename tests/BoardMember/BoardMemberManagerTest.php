<?php

namespace Tests\AppBundle\Repository;

use AppBundle\BoardMember\BoardMemberFilter;
use AppBundle\BoardMember\BoardMemberManager;
use AppBundle\Collection\AdherentCollection;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadBoardMemberRoleData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\BoardMember\Role;
use AppBundle\Repository\AdherentRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class BoardMemberManagerTest extends SqliteWebTestCase
{
    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var BoardMemberManager
     */
    private $boardMemberManager;

    use ControllerTestTrait;

    public function testSearchMembers()
    {
        $filter = BoardMemberFilter::createFromArray([]);
        $excludedMember = $this->getAdherentRepository()->findByEmail('kiroule.p@blabla.tld');

        $members = $this->boardMemberManager->paginateMembers($filter, $excludedMember);

        $this->assertCount(5, $members);
        $this->assertContainsOnlyInstancesOf(Adherent::class, $members);
        $this->assertNotContains($excludedMember, $members);
    }

    public function testPaginateMembers()
    {
        $filter = BoardMemberFilter::createFromArray([]);
        $excludedMember = $this->getAdherentRepository()->findByEmail('kiroule.p@blabla.tld');

        $paginator = $this->boardMemberManager->paginateMembers($filter, $excludedMember);

        $this->assertInstanceOf(Paginator::class, $paginator);
        $this->assertCount(5, $paginator);
        $this->assertContainsOnlyInstancesOf(Adherent::class, $paginator);
        $this->assertNotContains($excludedMember, $paginator);
    }

    public function testFindSavedMembers()
    {
        $adherent = $this->adherentRepository->findByUuid(LoadAdherentData::ADHERENT_12_UUID);

        $savedMembers = $this->boardMemberManager->findSavedMembers($adherent);

        $this->assertInstanceOf(AdherentCollection::class, $savedMembers);
        $this->assertCount(4, $savedMembers);
        $this->assertContainsOnlyInstancesOf(Adherent::class, $savedMembers);

        $expectedSavedMembers = [
            $this->adherentRepository->findByUuid(LoadAdherentData::ADHERENT_2_UUID),
            $this->adherentRepository->findByUuid(LoadAdherentData::ADHERENT_9_UUID),
            $this->adherentRepository->findByUuid(LoadAdherentData::ADHERENT_10_UUID),
            $this->adherentRepository->findByUuid(LoadAdherentData::ADHERENT_11_UUID),
        ];

        foreach ($expectedSavedMembers as $expectedSavedMember) {
            $this->assertContains($expectedSavedMember, $savedMembers);
        }
    }

    public function testFindRoles()
    {
        $roles = $this->boardMemberManager->findRoles();

        $this->assertCount(15, $roles);
        $this->assertContainsOnlyInstancesOf(Role::class, $roles);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
            LoadBoardMemberRoleData::class,
        ]);

        $this->container = $this->getContainer();
        $this->adherentRepository = $this->getAdherentRepository();
        $this->boardMemberManager = $this->container->get('app.board_member.manager');
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->boardMemberManager = null;
        $this->container = null;

        parent::tearDown();
    }
}

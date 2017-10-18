<?php

namespace Tests\AppBundle\Repository;

use AppBundle\BoardMember\BoardMemberFilter;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadBoardMemberRoleData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\Entity\Adherent;
use AppBundle\Entity\Event;
use AppBundle\Repository\AdherentRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class AdherentRepositoryTest extends SqliteWebTestCase
{
    /**
     * @var AdherentRepository
     */
    private $repository;

    use ControllerTestTrait;

    public function testLoadUserByUsername()
    {
        $this->assertInstanceOf(
            Adherent::class,
            $this->repository->loadUserByUsername('carl999@example.fr'),
            'Enabled adherent must be returned.'
        );

        $this->assertNull(
            $this->repository->loadUserByUsername('michelle.dufour@example.ch'),
            'Disabled adherent must not be returned.'
        );

        $this->assertNull(
            $this->repository->loadUserByUsername('someone@foobar.tld'),
            'Non registered adherent must not be returned.'
        );
    }

    public function testCountActiveAdherents()
    {
        $this->assertSame(15, $this->repository->countActiveAdherents());
    }

    public function testFindAllManagedBy()
    {
        $referent = $this->repository->loadUserByUsername('referent@en-marche-dev.fr');

        $this->assertInstanceOf(Adherent::class, $referent, 'Enabled referent must be returned.');

        $managedByReferent = $this->repository->findAllManagedBy($referent);

        $this->assertCount(7, $managedByReferent, 'Referent should manage 6 adherents + himself in his area.');
        $this->assertSame('Damien SCHMIDT', $managedByReferent[0]->getFullName());
        $this->assertSame('Michel VASSEUR', $managedByReferent[1]->getFullName());
        $this->assertSame('Michelle Dufour', $managedByReferent[2]->getFullName());
        $this->assertSame('Francis Brioul', $managedByReferent[3]->getFullName());
        $this->assertSame('Referent Referent', $managedByReferent[4]->getFullName());
        $this->assertSame('Benjamin Duroc', $managedByReferent[5]->getFullName());
        $this->assertSame('Gisele Berthoux', $managedByReferent[6]->getFullName());
    }

    public function testFindByEvent()
    {
        $event = $this->getEventRepository()->findOneBy(['uuid' => LoadEventData::EVENT_2_UUID]);

        $this->assertInstanceOf(Event::class, $event, 'Event must be returned.');

        $adherents = $this->repository->findByEvent($event);

        $this->assertCount(2, $adherents);
        $this->assertSame('Jacques Picard', $adherents[0]->getFullName());
        $this->assertSame('Francis Brioul', $adherents[1]->getFullName());
    }

    /**
     * @dataProvider dataProviderSearchBoardMembers
     */
    public function testSearchBoardMembers(array $filters, array $results)
    {
        $filter = BoardMemberFilter::createFromArray($filters);
        $excludedMember = $this->getAdherentRepository()->findByEmail('kiroule.p@blabla.tld');

        $boardMembers = $this->repository->searchBoardMembers($filter, $excludedMember);

        $this->assertCount(count($results), $boardMembers);

        foreach ($boardMembers as $key => $adherent) {
            $this->assertSame($results[$key], $adherent->getEmailAddress());
        }
    }

    /**
     * @dataProvider dataProviderSearchBoardMembers
     */
    public function testPaginateBoardMembers(array $filters, array $results)
    {
        $filter = BoardMemberFilter::createFromArray($filters);
        $excludedMember = $this->getAdherentRepository()->findByEmail('kiroule.p@blabla.tld');

        $boardMembers = $this->repository->paginateBoardMembers($filter, $excludedMember);

        $this->assertInstanceOf(Paginator::class, $boardMembers);
        $this->assertCount(count($results), $boardMembers);

        foreach ($boardMembers as $key => $adherent) {
            $this->assertSame($results[$key], $adherent->getEmailAddress());
        }
    }

    public function dataProviderSearchBoardMembers()
    {
        return [
            // Gender
            [
                ['g' => 'female'],
                ['laura@deloche.com', 'martine.lindt@gmail.com', 'lolodie.dutemps@hotnix.tld'],
            ],
            [
                ['g' => 'male'],
                ['referent@en-marche-dev.fr'],
            ],
            // Age
            [
                ['amin' => 55],
                ['referent@en-marche-dev.fr'],
            ],
            [
                ['amax' => 54],
                ['laura@deloche.com', 'martine.lindt@gmail.com', 'lolodie.dutemps@hotnix.tld'],
            ],
            [
                ['amin' => 55, 'amax' => 60],
                ['referent@en-marche-dev.fr'],
            ],
            // Name
            [
                ['f' => 'Laura'],
                ['laura@deloche.com'],
            ],
            [
                ['l' => 'Lindt'],
                ['martine.lindt@gmail.com'],
            ],
            [
                ['f' => 'Élodie', 'l' => 'Dutemps'],
                ['lolodie.dutemps@hotnix.tld'],
            ],
            // Location
            [
                ['p' => '76, 368645'],
                ['laura@deloche.com', 'lolodie.dutemps@hotnix.tld'],
            ],
            [
                ['a' => ['metropolitan']],
                ['laura@deloche.com', 'referent@en-marche-dev.fr'],
            ],
            // Role
            [
                ['r' => ['referent']],
                ['referent@en-marche-dev.fr'],
            ],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
            LoadBoardMemberRoleData::class,
            LoadEventCategoryData::class,
            LoadEventData::class,
        ]);

        $this->container = $this->getContainer();
        $this->repository = $this->getAdherentRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->repository = null;
        $this->container = null;

        parent::tearDown();
    }
}

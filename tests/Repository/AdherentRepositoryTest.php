<?php

namespace Tests\App\Repository;

use App\BoardMember\BoardMemberFilter;
use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeData;
use App\DataFixtures\ORM\LoadPhoningCampaignData;
use App\Entity\Adherent;
use App\Entity\Committee;
use App\Entity\Phoning\Campaign;
use App\Entity\ReferentTag;
use App\Repository\AdherentRepository;
use App\Repository\Phoning\CampaignRepository;
use App\Repository\ReferentTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Tests\App\AbstractKernelTestCase;

/**
 * @group functional
 */
class AdherentRepositoryTest extends AbstractKernelTestCase
{
    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var ReferentTagRepository
     */
    private $referentTagRepository;

    /**
     * @var CampaignRepository
     */
    private $campaignRepository;

    public function testLoadUserByUsername()
    {
        $this->assertInstanceOf(
            Adherent::class,
            $this->adherentRepository->loadUserByUsername('carl999@example.fr'),
            'Enabled adherent must be returned.'
        );

        $this->assertInstanceOf(
            Adherent::class,
            $this->adherentRepository->loadUserByUsername('michelle.dufour@example.ch'),
            'Disabled adherent must be returned.'
        );

        $this->assertNull(
            $this->adherentRepository->loadUserByUsername('someone@foobar.tld'),
            'Non registered adherent must not be returned.'
        );
    }

    public function testCountActiveAdherents()
    {
        self::assertSame(62, $this->adherentRepository->countActiveAdherents());
    }

    public function testFindAllManagedBy()
    {
        $referent = $this->adherentRepository->loadUserByUsername('referent@en-marche-dev.fr');

        $this->assertInstanceOf(Adherent::class, $referent, 'Enabled referent must be returned.');

        $managedByReferent = $this->adherentRepository->findAllManagedBy($referent);

        $this->assertCount(11, $managedByReferent, 'Referent should manage 9 adherents + himself in his area.');
        $this->assertSame('Damien SCHMIDT', $managedByReferent[0]->getFullName());
        $this->assertSame('Michel VASSEUR', $managedByReferent[1]->getFullName());
        $this->assertSame('Michelle Dufour', $managedByReferent[2]->getFullName());
        $this->assertSame('Député CHLI FDESIX', $managedByReferent[3]->getFullName());
        $this->assertSame('Thomas Leclerc', $managedByReferent[4]->getFullName());
        $this->assertSame('Laura Deloche', $managedByReferent[5]->getFullName());
        $this->assertSame('Francis Brioul', $managedByReferent[6]->getFullName());
        $this->assertSame('Referent Referent', $managedByReferent[7]->getFullName());
        $this->assertSame('Referent child Referent child', $managedByReferent[8]->getFullName());
        $this->assertSame('Benjamin Duroc', $managedByReferent[9]->getFullName());
        $this->assertSame('Gisele Berthoux', $managedByReferent[10]->getFullName());
    }

    /**
     * @dataProvider dataProviderSearchBoardMembers
     */
    public function testSearchBoardMembers(array $filters, array $results)
    {
        $filter = BoardMemberFilter::createFromArray($filters);
        $excludedMember = $this->getAdherentRepository()->findOneByEmail('kiroule.p@blabla.tld');

        $boardMembers = $this->adherentRepository->searchBoardMembers($filter, $excludedMember);

        $this->assertSameSize($results, $boardMembers);

        foreach ($boardMembers as $adherent) {
            $this->assertContains($adherent->getEmailAddress(), $results);
        }
    }

    /**
     * @dataProvider dataProviderSearchBoardMembers
     */
    public function testPaginateBoardMembers(array $filters, array $results)
    {
        $filter = BoardMemberFilter::createFromArray($filters);
        $excludedMember = $this->getAdherentRepository()->findOneByEmail('kiroule.p@blabla.tld');

        $boardMembers = $this->adherentRepository->paginateBoardMembers($filter, $excludedMember);

        $this->assertInstanceOf(Paginator::class, $boardMembers);
        $this->assertSameSize($results, $boardMembers);

        foreach ($boardMembers as $adherent) {
            $this->assertContains($adherent->getEmailAddress(), $results);
        }
    }

    public function testFindReferentsByCommittee()
    {
        $committeeTags = new ArrayCollection([
            $this->referentTagRepository->findOneByCode('CH'),
        ]);

        // Foreign Committee with Referent
        $committee = $this->createMock(Committee::class);
        $committee->expects(static::any())->method('getReferentTags')->willReturn($committeeTags);

        $referents = $this->adherentRepository->findReferentsByCommittee($committee);

        $this->assertNotEmpty($referents);
        $this->assertCount(2, $referents);

        $referent = $referents->first();

        $this->assertSame('Referent Referent', $referent->getFullName());
        $this->assertSame('referent@en-marche-dev.fr', $referent->getEmailAddress());

        // Committee with no Referent
        $committeeTags = new ArrayCollection([
            $this->referentTagRepository->findOneByCode('44'),
        ]);

        $committee = $this->createMock(Committee::class);
        $committee->expects(static::any())->method('getReferentTags')->willReturn($committeeTags);

        $referents = $this->adherentRepository->findReferentsByCommittee($committee);

        $this->assertEmpty($referents);

        // Departmental Committee with Referent
        $committeeTags = new ArrayCollection([
            $this->referentTagRepository->findOneByCode('77'),
        ]);

        $committee = $this->createMock(Committee::class);
        $committee->expects(static::any())->method('getReferentTags')->willReturn($committeeTags);

        $referents = $this->adherentRepository->findReferentsByCommittee($committee);

        $this->assertCount(2, $referents);
        $this->assertSame('referent@en-marche-dev.fr', $referents[0]->getEmailAddress());
        $this->assertSame('referent-75-77@en-marche-dev.fr', $referents[1]->getEmailAddress());
    }

    public function testFindCommitteeHostMembersList()
    {
        // Approved committees
        $this->assertCount(2, $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)), '1 supervisor + 1 host');
        $this->assertCount(4, $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeData::COMMITTEE_3_UUID)), '1 supervisor + 1 host');
        $this->assertCount(1, $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeData::COMMITTEE_4_UUID)), '1 supervisor');
        $this->assertCount(2, $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeData::COMMITTEE_5_UUID)), '1 supervisor');

        // Unapproved committees
        $this->assertCount(0, $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeData::COMMITTEE_2_UUID)));
    }

    public function testCountHostMembersInCommittee()
    {
        $this->assertSame(2, $this->adherentRepository->countCommitteeHosts($this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)));
        $this->assertSame(4, $this->adherentRepository->countCommitteeHosts($this->getCommittee(LoadCommitteeData::COMMITTEE_3_UUID)));
        $this->assertSame(1, $this->adherentRepository->countCommitteeHosts($this->getCommittee(LoadCommitteeData::COMMITTEE_4_UUID)));
    }

    public function testCountSupervisorMembersInCommittee()
    {
        $this->assertSame(1, $this->adherentRepository->countCommitteeSupervisors($this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)));
        $this->assertSame(3, $this->adherentRepository->countCommitteeSupervisors($this->getCommittee(LoadCommitteeData::COMMITTEE_3_UUID)));
        $this->assertSame(2, $this->adherentRepository->countCommitteeSupervisors($this->getCommittee(LoadCommitteeData::COMMITTEE_7_UUID)));
    }

    public function testMemberIsCommitteeHost()
    {
        $this->assertTrue($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID)));
        $this->assertTrue($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID), $this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)));
        $this->assertFalse($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID), $this->getCommittee(LoadCommitteeData::COMMITTEE_2_UUID)));

        $this->assertTrue($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID)));
        $this->assertTrue($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID), $this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID)));
        $this->assertFalse($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID), $this->getCommittee(LoadCommitteeData::COMMITTEE_2_UUID)));

        $this->assertFalse($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_1_UUID)));
        $this->assertFalse($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_2_UUID)));
        $this->assertFalse($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID)));
    }

    /**
     * @dataProvider dataProviderExcludedCampaignAdherents
     */
    public function testFindOneToCall(
        string $campaignUuid,
        string $excludedAdherent,
        string $assert,
        array $adherentEmails
    ): void {
        $excludedAdherent = $this->adherentRepository->findOneByEmail($excludedAdherent);
        $campaign = $this->campaignRepository->findOneBy(['uuid' => $campaignUuid]);
        $adherent = $this->adherentRepository->findOneToCall($campaign, $excludedAdherent);

        /** @var Adherent $adherent */
        $this->assertNotNull($adherent);
        $this->$assert($adherent->getEmailAddress(), $adherentEmails);
        $audience = $campaign->getAudience();
        if ($gender = $audience->getGender()) {
            $this->assertSame($gender, $adherent->getGender());
        }
    }

    public function dataProviderExcludedCampaignAdherents(): \Generator
    {
        yield [LoadPhoningCampaignData::CAMPAIGN_1_UUID, 'jacques.picard@en-marche.fr', 'assertNotContains', [
            'adherent-male-a@en-marche-dev.fr',
            'adherent-male-b@en-marche-dev.fr',
            'adherent-male-c@en-marche-dev.fr',
            'adherent-male-d@en-marche-dev.fr',
            'adherent-male-e@en-marche-dev.fr',
            'adherent-female-f@en-marche-dev.fr',
            'adherent-male-33@en-marche-dev.fr',
            'adherent-male-35@en-marche-dev.fr',
            'adherent-male-37@en-marche-dev.fr',
            'adherent-male-39@en-marche-dev.fr',
            'adherent-male-41@en-marche-dev.fr',
            'adherent-male-43@en-marche-dev.fr',
            'adherent-male-45@en-marche-dev.fr',
            'adherent-male-47@en-marche-dev.fr',
            'adherent-male-49@en-marche-dev.fr',
        ]];
        yield [LoadPhoningCampaignData::CAMPAIGN_5_UUID, 'jacques.picard@en-marche.fr', 'assertContains', [
            'benjyd@aol.com',
        ]];
    }

    public function dataProviderSearchBoardMembers(): array
    {
        return [
            // Gender
            [
                ['g' => 'female'],
                ['laura@deloche.com', 'martine.lindt@gmail.com', 'lolodie.dutemps@hotnix.tld'],
            ],
            [
                ['g' => 'male'],
                ['carl999@example.fr', 'deputy@en-marche-dev.fr', 'deputy-ch-li@en-marche-dev.fr', 'referent@en-marche-dev.fr', 'deputy-75-2@en-marche-dev.fr'],
            ],
            // Age
            [
                ['amin' => 55],
                ['carl999@example.fr', 'referent@en-marche-dev.fr'],
            ],
            [
                ['amax' => 54],
                ['deputy@en-marche-dev.fr', 'deputy-ch-li@en-marche-dev.fr', 'laura@deloche.com', 'martine.lindt@gmail.com', 'lolodie.dutemps@hotnix.tld', 'deputy-75-2@en-marche-dev.fr'],
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
                ['laura@deloche.com', 'deputy@en-marche-dev.fr', 'referent@en-marche-dev.fr'],
            ],
            // Role
            [
                ['r' => ['referent']],
                ['referent@en-marche-dev.fr'],
            ],
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();
        $this->referentTagRepository = $this->getRepository(ReferentTag::class);
        $this->campaignRepository = $this->getRepository(Campaign::class);
    }

    protected function tearDown(): void
    {
        $this->adherentRepository = null;
        $this->referentTagRepository = null;
        $this->campaignRepository = null;

        parent::tearDown();
    }
}

<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeV1Data;
use App\DataFixtures\ORM\LoadPhoningCampaignData;
use App\DataFixtures\ORM\LoadVotingPlatformElectionData;
use App\Entity\Adherent;
use App\Entity\Phoning\Campaign;
use App\Entity\VotingPlatform\Election;
use App\Entity\VotingPlatform\Vote;
use App\Entity\VotingPlatform\Voter;
use App\Repository\AdherentRepository;
use App\Repository\Phoning\CampaignRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
class AdherentRepositoryTest extends AbstractKernelTestCase
{
    /**
     * @var AdherentRepository
     */
    private $adherentRepository;

    /**
     * @var CampaignRepository
     */
    private $campaignRepository;

    public function testLoadUserByUsername(): void
    {
        $this->assertInstanceOf(
            Adherent::class,
            $this->adherentRepository->loadUserByIdentifier('carl999@example.fr'),
            'Enabled adherent must be returned.'
        );

        $this->assertInstanceOf(
            Adherent::class,
            $this->adherentRepository->loadUserByIdentifier('michelle.dufour@example.ch'),
            'Disabled adherent must be returned.'
        );

        $this->expectException(UserNotFoundException::class);
        $this->adherentRepository->loadUserByIdentifier('someone@foobar.tld');
    }

    public function testFindCommitteeHostMembersList(): void
    {
        // Approved committees
        $this->assertCount(1, $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID)), '1 supervisor + 1 host');
        $this->assertCount(4, $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_3_UUID)), '1 supervisor + 1 host');
        $this->assertCount(1, $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_4_UUID)), '1 supervisor');
        $this->assertCount(2, $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_5_UUID)), '1 supervisor');

        // Unapproved committees
        $this->assertCount(0, $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_2_UUID)));
    }

    public function testCountHostMembersInCommittee(): void
    {
        $this->assertSame(1, $this->adherentRepository->countCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID)));
        $this->assertSame(4, $this->adherentRepository->countCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_3_UUID)));
        $this->assertSame(1, $this->adherentRepository->countCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_4_UUID)));
    }

    public function testMemberIsCommitteeHost(): void
    {
        $this->assertTrue($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID)));
        $this->assertTrue($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID), $this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID)));
        $this->assertFalse($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_3_UUID), $this->getCommittee(LoadCommitteeV1Data::COMMITTEE_2_UUID)));

        $this->assertTrue($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID)));
        $this->assertFalse($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID), $this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID)));
        $this->assertFalse($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_5_UUID), $this->getCommittee(LoadCommitteeV1Data::COMMITTEE_2_UUID)));

        $this->assertFalse($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_1_UUID)));
        $this->assertFalse($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_2_UUID)));
        $this->assertFalse($this->adherentRepository->hostCommittee($this->getAdherent(LoadAdherentData::ADHERENT_4_UUID)));
    }

    #[DataProvider('dataProviderExcludedCampaignAdherents')]
    public function testFindOneToCall(
        string $campaignUuid,
        string $excludedAdherent,
        string $assert,
        array $adherentEmails,
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

    public static function dataProviderExcludedCampaignAdherents(): \Generator
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

    public function testFindRenaissanceAdherentsForElectionReturnsKnownRenaissanceAdherents(): void
    {
        $election = $this->getConsultationElection();

        $adherents = $this->adherentRepository->findRenaissanceAdherentsForElection($election);

        self::assertNotEmpty($adherents, 'Expected at least one Renaissance adherent in fixtures');

        $emails = $this->extractEmails($adherents);
        // jacques.picard@en-marche.fr is enabled with tag adherent:a_jour_2024 (cf. LoadAdherentData adherent-3)
        self::assertContains('jacques.picard@en-marche.fr', $emails);
    }

    public function testFindRenaissanceAdherentsForElectionExcludesSympathisants(): void
    {
        $election = $this->getConsultationElection();
        $emails = $this->extractEmails($this->adherentRepository->findRenaissanceAdherentsForElection($election));

        self::assertNotContains('carl999@example.fr', $emails, 'Sympathisant must be excluded');
    }

    public function testFindRenaissanceAdherentsForElectionExcludesDisabledAdherents(): void
    {
        $election = $this->getConsultationElection();
        $emails = $this->extractEmails($this->adherentRepository->findRenaissanceAdherentsForElection($election));

        self::assertNotContains('michelle.dufour@example.ch', $emails, 'Disabled adherent must be excluded');
    }

    public function testFindRenaissanceAdherentsForElectionRespectsAccountCreationDeadline(): void
    {
        $election = $this->getConsultationElection();
        $designation = $election->getDesignation();

        $deadline = new \DateTime('2017-01-04 00:00:00');
        $designation->accountCreationDeadline = $deadline;

        $emails = $this->extractEmails($this->adherentRepository->findRenaissanceAdherentsForElection($election));

        // jacques.picard@en-marche.fr is registered_at 2017-01-03, must be kept
        self::assertContains('jacques.picard@en-marche.fr', $emails);
        // gisele-berthoux@caramail.com is registered_at 2017-01-08, must be excluded
        self::assertNotContains('gisele-berthoux@caramail.com', $emails);
    }

    public function testFindRenaissanceAdherentsForElectionRespectsMembershipDeadline(): void
    {
        $election = $this->getConsultationElection();
        $designation = $election->getDesignation();

        $designation->membershipDeadline = new \DateTime('1970-01-01 00:00:00');

        // Adherents whose lastMembershipDonation is after 1970 are excluded; those with NULL
        // lastMembershipDonation are kept (cf. method implementation).
        foreach ($this->adherentRepository->findRenaissanceAdherentsForElection($election) as $adherent) {
            self::assertNull(
                $adherent->getLastMembershipDonation(),
                "Adherent {$adherent->getEmailAddress()} should have been excluded by the membershipDeadline filter",
            );
        }
    }

    public function testFindRenaissanceAdherentsForElectionExcludeVotedFiltersOutVoters(): void
    {
        $election = $this->getConsultationElection();

        $before = $this->extractEmails($this->adherentRepository->findRenaissanceAdherentsForElection($election, false));

        $jacques = $this->adherentRepository->findOneByEmail('jacques.picard@en-marche.fr');
        $voter = $this->getEntityManager()->getRepository(Voter::class)->findForAdherent($jacques);

        // Simulate a vote on the current round
        $vote = new Vote($voter, $election->getCurrentRound());
        $this->getEntityManager()->persist($vote);
        $this->getEntityManager()->flush();

        $after = $this->extractEmails($this->adherentRepository->findRenaissanceAdherentsForElection($election, true));

        self::assertContains('jacques.picard@en-marche.fr', $before);
        self::assertNotContains('jacques.picard@en-marche.fr', $after);

        $this->getEntityManager()->remove($vote);
        $this->getEntityManager()->flush();
    }

    public function testFindRenaissanceAdherentsForElectionPaginationIsStableAndDisjoint(): void
    {
        $election = $this->getConsultationElection();

        $batch1 = $this->adherentRepository->findRenaissanceAdherentsForElection($election, false, 0, 3);
        $batch2 = $this->adherentRepository->findRenaissanceAdherentsForElection($election, false, 3, 3);

        $ids1 = array_map(fn (Adherent $a) => $a->getId(), $batch1);
        $ids2 = array_map(fn (Adherent $a) => $a->getId(), $batch2);

        self::assertEmpty(array_intersect($ids1, $ids2), 'Pagination batches must be disjoint');
        // Order is ascending on adherent.id
        if (3 === \count($ids1) && !empty($ids2)) {
            self::assertGreaterThan(max($ids1), min($ids2), 'Batch 2 ids must come after batch 1 ids');
        }
    }

    private function getConsultationElection(): Election
    {
        $election = $this->getEntityManager()->getRepository(Election::class)->findOneBy([
            'uuid' => LoadVotingPlatformElectionData::ELECTION_UUID14,
        ]);

        self::assertNotNull($election, 'Consultation fixture election (UUID14) must be loaded');

        return $election;
    }

    private function extractEmails(array $adherents): array
    {
        return array_map(fn (Adherent $a) => $a->getEmailAddress(), $adherents);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adherentRepository = $this->getAdherentRepository();
        $this->campaignRepository = $this->getRepository(Campaign::class);
    }

    protected function tearDown(): void
    {
        $this->adherentRepository = null;
        $this->campaignRepository = null;

        parent::tearDown();
    }
}

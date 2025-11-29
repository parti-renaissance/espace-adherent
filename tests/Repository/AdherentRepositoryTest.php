<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\DataFixtures\ORM\LoadAdherentData;
use App\DataFixtures\ORM\LoadCommitteeV1Data;
use App\DataFixtures\ORM\LoadPhoningCampaignData;
use App\Entity\Adherent;
use App\Entity\Phoning\Campaign;
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

    public function testLoadUserByUsername()
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

    public function testFindCommitteeHostMembersList()
    {
        // Approved committees
        $this->assertCount(1, $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID)), '1 supervisor + 1 host');
        $this->assertCount(4, $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_3_UUID)), '1 supervisor + 1 host');
        $this->assertCount(1, $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_4_UUID)), '1 supervisor');
        $this->assertCount(2, $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_5_UUID)), '1 supervisor');

        // Unapproved committees
        $this->assertCount(0, $this->adherentRepository->findCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_2_UUID)));
    }

    public function testCountHostMembersInCommittee()
    {
        $this->assertSame(1, $this->adherentRepository->countCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID)));
        $this->assertSame(4, $this->adherentRepository->countCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_3_UUID)));
        $this->assertSame(1, $this->adherentRepository->countCommitteeHosts($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_4_UUID)));
    }

    public function testCountSupervisorMembersInCommittee()
    {
        $this->assertSame(1, $this->adherentRepository->countCommitteeSupervisors($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_1_UUID)));
        $this->assertSame(3, $this->adherentRepository->countCommitteeSupervisors($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_3_UUID)));
        $this->assertSame(2, $this->adherentRepository->countCommitteeSupervisors($this->getCommittee(LoadCommitteeV1Data::COMMITTEE_7_UUID)));
    }

    public function testMemberIsCommitteeHost()
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

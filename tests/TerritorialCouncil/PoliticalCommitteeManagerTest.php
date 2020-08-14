<?php

namespace Tests\App\Consumer;

use App\Entity\Adherent;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\PoliticalCommitteeMembership;
use App\Entity\TerritorialCouncil\PoliticalCommitteeQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\TerritorialCouncil\PoliticalCommitteeManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class PoliticalCommitteeManagerTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface */
    private $entityManager;

    public function setUp()
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this
            ->entityManager
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->createMock(Connection::class))
        ;
    }

    public function tearDown()
    {
        $this->entityManager = null;
    }

    public function testCreateMembershipSuccessfully(): void
    {
        $adherent = new Adherent();
        $politicalCommittee = new PoliticalCommittee('PC name', new TerritorialCouncil());
        $qualityName = TerritorialCouncilQualityEnum::SENATOR;

        $politicalCommitteeManager = new PoliticalCommitteeManager($this->entityManager);

        $membership = $politicalCommitteeManager->createMembership($adherent, $politicalCommittee, $qualityName);

        $this->assertSame([$qualityName], $membership->getQualityNames());
        $this->assertSame($adherent, $membership->getAdherent());
        $this->assertSame($politicalCommittee, $membership->getPoliticalCommittee());
    }

    /**
     * @expectedException \App\TerritorialCouncil\Exception\TerritorialCouncilQualityException
     */
    public function testCannotCreateMembershipWithInvalidQualityName(): void
    {
        $adherent = new Adherent();
        $politicalCommittee = new PoliticalCommittee('PC name', new TerritorialCouncil());
        $qualityName = 'invalid';

        $politicalCommitteeManager = new PoliticalCommitteeManager($this->entityManager);

        $politicalCommitteeManager->createMembership($adherent, $politicalCommittee, $qualityName);
    }

    public function testAddPoliticalCommitteeQualityDoNothingWhenNoPoliticalCommitteeMembership(): void
    {
        $adherent = new Adherent();
        $qualityName = TerritorialCouncilQualityEnum::SENATOR;

        $politicalCommitteeManager = new PoliticalCommitteeManager($this->entityManager);

        $politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName);

        $this->assertSame(null, $adherent->getPoliticalCommitteeMembership());
    }

    public function testAddPoliticalCommitteeQualityWithoutCheck(): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);
        $qualityName = TerritorialCouncilQualityEnum::SENATOR;

        $politicalCommitteeManager = new PoliticalCommitteeManager($this->entityManager);

        $politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName);

        $this->assertSame([$qualityName], $adherent->getPoliticalCommitteeMembership()->getQualityNames());
    }

    /**
     * @dataProvider provideOfficioQualities
     */
    public function testAddPoliticalCommitteeQualityWithCheckWhenOfficioQualities(string $qualityName): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);

        $politicalCommitteeManager = new PoliticalCommitteeManager($this->entityManager);

        $politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName, true);

        $this->assertSame([$qualityName], $adherent->getPoliticalCommitteeMembership()->getQualityNames());
    }

    /**
     * @dataProvider provideNotOfficioQualities
     */
    public function testCannotAddPoliticalCommitteeQualityWithCheckWhenNotOfficioQualities(string $qualityName): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);

        $politicalCommitteeManager = new PoliticalCommitteeManager($this->entityManager);

        $politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName, true);

        $this->assertSame([], $adherent->getPoliticalCommitteeMembership()->getQualityNames());
    }

    public function testRemovePoliticalCommitteeQualitySuccessfully(): void
    {
        $qualityName = TerritorialCouncilQualityEnum::SENATOR;
        $membership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $membership->addQuality(new PoliticalCommitteeQuality($qualityName));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($membership);

        $politicalCommitteeManager = new PoliticalCommitteeManager($this->entityManager);

        $politicalCommitteeManager->removePoliticalCommitteeQuality($adherent, $qualityName);

        $this->assertSame([], $adherent->getPoliticalCommitteeMembership()->getQualityNames());
    }

    public function testRemovePoliticalCommitteeQualityDoNothingWhenNoPoliticalCommitteeMembership(): void
    {
        $adherent = new Adherent();

        $politicalCommitteeManager = new PoliticalCommitteeManager($this->entityManager);

        $politicalCommitteeManager->removePoliticalCommitteeQuality($adherent, TerritorialCouncilQualityEnum::SENATOR);

        $this->assertSame(null, $adherent->getPoliticalCommitteeMembership());
    }

    public function testCreateMembershipFromTerritorialCouncilMembershipSuccessfully(): void
    {
        $territorialCouncil = new TerritorialCouncil();
        $politicalCommittee = new PoliticalCommittee('PC name', $territorialCouncil);
        $territorialCouncil->setPoliticalCommittee($politicalCommittee);
        $tcMembership = new TerritorialCouncilMembership();
        $tcMembership->setTerritorialCouncil($territorialCouncil);
        $tcMembership->setAdherent(new Adherent());
        $tcMembership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::SENATOR, 'TC zone'));
        $tcMembership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR, 'Committee'));

        $this->entityManager
            ->expects($this->any())
            ->method('persist')
            ->with($this->isInstanceOf(PoliticalCommitteeMembership::class))
        ;
        $this->entityManager
            ->expects($this->any())
            ->method('flush')
        ;
        $politicalCommitteeManager = new PoliticalCommitteeManager($this->entityManager);

        $politicalCommitteeManager->createMembershipFromTerritorialCouncilMembership($tcMembership);

        $this->assertSame(null, $tcMembership->getAdherent()->getPoliticalCommitteeMembership());
    }

    public function testCannotCreateMembershipFromTerritorialCouncilMembershipIfNoOfficioQualities(): void
    {
        $territorialCouncil = new TerritorialCouncil();
        $politicalCommittee = new PoliticalCommittee('PC name', $territorialCouncil);
        $territorialCouncil->setPoliticalCommittee($politicalCommittee);
        $tcMembership = new TerritorialCouncilMembership();
        $tcMembership->setTerritorialCouncil($territorialCouncil);
        $tcMembership->setAdherent(new Adherent());
        $tcMembership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR, 'Committee'));

        $this->entityManager
            ->expects($this->never())
            ->method('persist')
        ;
        $this->entityManager
            ->expects($this->never())
            ->method('flush')
        ;
        $politicalCommitteeManager = new PoliticalCommitteeManager($this->entityManager);

        $politicalCommitteeManager->createMembershipFromTerritorialCouncilMembership($tcMembership);
    }

    public function testHandleTerritorialCouncilMembershipUpdateDoNothing(): void
    {
        $adherent = new Adherent();

        $politicalCommitteeManager = new PoliticalCommitteeManager($this->entityManager);

        $politicalCommitteeManager->handleTerritorialCouncilMembershipUpdate($adherent, null);

        $this->assertSame(null, $adherent->getPoliticalCommitteeMembership());
    }

    public function testHandleTerritorialCouncilMembershipUpdateRemovePCMembership(): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);

        $this->entityManager
            ->expects($this->any())
            ->method('flush')
        ;
        $politicalCommitteeManager = new PoliticalCommitteeManager($this->entityManager);

        $politicalCommitteeManager->handleTerritorialCouncilMembershipUpdate($adherent, null);

        $this->assertSame(null, $adherent->getPoliticalCommitteeMembership());
    }

    public function testHandleTerritorialCouncilMembershipUpdateRemovePCMembershipSuccessfully(): void
    {
        $territorialCouncil = new TerritorialCouncil();
        $politicalCommittee = new PoliticalCommittee('PC name', $territorialCouncil);
        $territorialCouncil->setPoliticalCommittee($politicalCommittee);
        $tcMembership = new TerritorialCouncilMembership();
        $tcMembership->setTerritorialCouncil($territorialCouncil);
        $tcMembership->setAdherent(new Adherent());
        $tcMembership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::SENATOR, 'TC zone'));
        $tcMembership->addQuality(new TerritorialCouncilQuality(TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR, 'Committee'));
        $adherent = new Adherent();
        $adherent->setTerritorialCouncilMembership($tcMembership);

        $this->entityManager
            ->expects($this->any())
            ->method('persist')
            ->with($this->isInstanceOf(PoliticalCommitteeMembership::class))
        ;
        $this->entityManager
            ->expects($this->any())
            ->method('flush')
        ;
        $politicalCommitteeManager = new PoliticalCommitteeManager($this->entityManager);

        $politicalCommitteeManager->handleTerritorialCouncilMembershipUpdate($adherent, null);

        $this->assertSame(null, $adherent->getPoliticalCommitteeMembership());
    }

    public function provideOfficioQualities(): iterable
    {
        yield [TerritorialCouncilQualityEnum::REFERENT];
        yield [TerritorialCouncilQualityEnum::GOVERNMENT_MEMBER];
        yield [TerritorialCouncilQualityEnum::REFERENT_JAM];
        yield [TerritorialCouncilQualityEnum::LRE_MANAGER];
        yield [TerritorialCouncilQualityEnum::SENATOR];
        yield [TerritorialCouncilQualityEnum::DEPUTY];
        yield [TerritorialCouncilQualityEnum::EUROPEAN_DEPUTY];
        yield [TerritorialCouncilQualityEnum::REGIONAL_COUNCIL_PRESIDENT];
        yield [TerritorialCouncilQualityEnum::DEPARTMENTAL_COUNCIL_PRESIDENT];
    }

    public function provideNotOfficioQualities(): iterable
    {
        yield [TerritorialCouncilQualityEnum::MAYOR];
        yield [TerritorialCouncilQualityEnum::REGIONAL_COUNCILOR];
        yield [TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR];
        yield [TerritorialCouncilQualityEnum::CITY_COUNCILOR];
        yield [TerritorialCouncilQualityEnum::CONSULAR_CONSELOR];
        yield [TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR];
        yield [TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT];
    }
}

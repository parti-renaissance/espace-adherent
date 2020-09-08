<?php

namespace Tests\App\Consumer;

use App\Entity\Adherent;
use App\Entity\AdherentMandate\TerritorialCouncilAdherentMandate;
use App\Entity\PostAddress;
use App\Entity\TerritorialCouncil\PoliticalCommittee;
use App\Entity\TerritorialCouncil\PoliticalCommitteeMembership;
use App\Entity\TerritorialCouncil\PoliticalCommitteeQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncil;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Entity\TerritorialCouncil\TerritorialCouncilQuality;
use App\Entity\TerritorialCouncil\TerritorialCouncilQualityEnum;
use App\Repository\AdherentMandate\TerritorialCouncilAdherentMandateRepository;
use App\Repository\ElectedRepresentative\MandateRepository;
use App\Repository\TerritorialCouncil\PoliticalCommitteeMembershipRepository;
use App\TerritorialCouncil\PoliticalCommitteeManager;
use App\ValueObject\Genders;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Translation\TranslatorInterface;

class PoliticalCommitteeManagerTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface */
    private $entityManager;
    /** @var \PHPUnit_Framework_MockObject_MockObject|MandateRepository */
    private $mandateRepository;
    /** @var \PHPUnit_Framework_MockObject_MockObject|PoliticalCommitteeMembershipRepository */
    private $membershipRepository;
    /** @var \PHPUnit_Framework_MockObject_MockObject|TerritorialCouncilAdherentMandateRepository */
    private $tcMandateRepository;
    /** @var \PHPUnit_Framework_MockObject_MockObject|TranslatorInterface */
    private $translator;
    /** @var PoliticalCommitteeManager */
    private $politicalCommitteeManager;

    public function setUp()
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this
            ->entityManager
            ->expects($this->any())
            ->method('getConnection')
            ->willReturn($this->createMock(Connection::class))
        ;

        $this->mandateRepository = $this->createMock(MandateRepository::class);
        $this->membershipRepository = $this->createMock(PoliticalCommitteeMembershipRepository::class);
        $this->tcMandateRepository = $this->createMock(TerritorialCouncilAdherentMandateRepository::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->politicalCommitteeManager = new PoliticalCommitteeManager(
            $this->entityManager,
            $this->mandateRepository,
            $this->membershipRepository,
            $this->tcMandateRepository,
            $this->translator
        );
    }

    public function tearDown()
    {
        $this->entityManager = null;
        $this->mandateRepository = null;
        $this->membershipRepository = null;
        $this->translator = null;
    }

    public function testCreateMembershipSuccessfully(): void
    {
        $adherent = new Adherent();
        $politicalCommittee = new PoliticalCommittee('PC name', new TerritorialCouncil());
        $qualityName = TerritorialCouncilQualityEnum::SENATOR;

        $membership = $this->politicalCommitteeManager->createMembership($adherent, $politicalCommittee, $qualityName);

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

        $this->politicalCommitteeManager->createMembership($adherent, $politicalCommittee, $qualityName);
    }

    public function testAddPoliticalCommitteeQualityDoNothingWhenNoPoliticalCommitteeMembership(): void
    {
        $adherent = new Adherent();
        $qualityName = TerritorialCouncilQualityEnum::SENATOR;

        $this->politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName, false);

        $this->assertSame(null, $adherent->getPoliticalCommitteeMembership());
    }

    public function testAddPoliticalCommitteeQualityWithoutCheck(): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);
        $qualityName = TerritorialCouncilQualityEnum::SENATOR;

        $this->politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName, false);

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

        $this->politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName);

        $this->assertSame([$qualityName], $adherent->getPoliticalCommitteeMembership()->getQualityNames());
    }

    /**
     * @dataProvider provideNotPassingQualities
     */
    public function testCannotAddPoliticalCommitteeQualityWithCheckWhenNotPassingQuality(string $qualityName): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);

        $this->politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName);

        $this->assertSame([], $adherent->getPoliticalCommitteeMembership()->getQualityNames());
    }

    /**
     * @dataProvider provideElectedMemberQualities
     */
    public function testCannotAddPoliticalCommitteeQualityWithCheckWhenElectedMemberQualityButNoTcMembership(
        string $qualityName
    ): void {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);

        $this->politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName);

        $this->assertSame([], $adherent->getPoliticalCommitteeMembership()->getQualityNames());
    }

    /**
     * @dataProvider provideElectedMemberQualities
     */
    public function testCannotAddPoliticalCommitteeQualityWithCheckWhenElectedMemberQualityButNoTcMandate(
        string $qualityName
    ): void {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership(new TerritorialCouncil()));

        $this->politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName);

        $this->assertSame([], $adherent->getPoliticalCommitteeMembership()->getQualityNames());
    }

    /**
     * @dataProvider provideElectedMemberQualities
     */
    public function testAddPoliticalCommitteeQualityWithCheckWhenElectedMemberQuality(string $qualityName): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $territorialCouncil = new TerritorialCouncil();
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($pcMembership);
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));

        $this->tcMandateRepository
            ->expects($this->once())
            ->method('findActiveMandateWithQuality')
            ->with($adherent, $territorialCouncil, $qualityName)
            ->willReturn(new TerritorialCouncilAdherentMandate($adherent, Genders::MALE, new \DateTime()))
        ;
        $this->politicalCommitteeManager->addPoliticalCommitteeQuality($adherent, $qualityName);

        $this->assertSame([$qualityName], $adherent->getPoliticalCommitteeMembership()->getQualityNames());
    }

    public function testRemovePoliticalCommitteeQualitySuccessfully(): void
    {
        $qualityName = TerritorialCouncilQualityEnum::SENATOR;
        $membership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $membership->addQuality(new PoliticalCommitteeQuality($qualityName));
        $adherent = new Adherent();
        $adherent->setPoliticalCommitteeMembership($membership);

        $this->politicalCommitteeManager->removePoliticalCommitteeQuality($adherent, $qualityName);

        $this->assertSame([], $adherent->getPoliticalCommitteeMembership()->getQualityNames());
    }

    public function testRemovePoliticalCommitteeQualityDoNothingWhenNoPoliticalCommitteeMembership(): void
    {
        $adherent = new Adherent();

        $this->politicalCommitteeManager->removePoliticalCommitteeQuality($adherent, TerritorialCouncilQualityEnum::SENATOR);

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
        $this->politicalCommitteeManager->createMembershipFromTerritorialCouncilMembership($tcMembership);

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
        $this->politicalCommitteeManager->createMembershipFromTerritorialCouncilMembership($tcMembership);
    }

    public function testHandleTerritorialCouncilMembershipUpdateDoNothing(): void
    {
        $adherent = new Adherent();

        $this->politicalCommitteeManager->handleTerritorialCouncilMembershipUpdate($adherent, null);

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
        $this->politicalCommitteeManager->handleTerritorialCouncilMembershipUpdate($adherent, null);

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
        $this->politicalCommitteeManager->handleTerritorialCouncilMembershipUpdate($adherent, null);

        $this->assertSame(null, $adherent->getPoliticalCommitteeMembership());
    }

    /**
     * @expectedException \App\TerritorialCouncil\Exception\PoliticalCommitteeMembershipException
     */
    public function testCannotCreateMayorOrLeaderMembershipIfNoTerritorialCouncilMembership(): void
    {
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $adherent = $this->createAdherent();

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('territorial_council.adherent_has_no_membership', $this->anything())
        ;
        $this->politicalCommitteeManager->createMayorOrLeaderMembership($territorialCouncil, $adherent);
    }

    /**
     * @expectedException \App\TerritorialCouncil\Exception\PoliticalCommitteeMembershipException
     */
    public function testCannotCreateMayorOrLeaderMembershipIfAdherentHasAlreadyPoliticalCommitteeMembership(): void
    {
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $adherent = $this->createAdherent();
        $adherent->setPoliticalCommitteeMembership(new PoliticalCommitteeMembership(new PoliticalCommittee('Test CoPol', new TerritorialCouncil())));
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('political_committee.membership.adherent_has_already', $this->anything())
        ;
        $this->politicalCommitteeManager->createMayorOrLeaderMembership($territorialCouncil, $adherent);
    }

    /**
     * @expectedException \App\TerritorialCouncil\Exception\PoliticalCommitteeMembershipException
     */
    public function testCannotCreateMayorOrLeaderMembershipIfMaxNumberExceeded(): void
    {
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $politicalCommittee = new PoliticalCommittee('Test CoPol', $territorialCouncil);
        $territorialCouncil->setPoliticalCommittee($politicalCommittee);
        $adherent = $this->createAdherent();
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));

        $this->membershipRepository
            ->expects($this->once())
            ->method('countLeaderAndMayorMembersFor')
            ->with($politicalCommittee)
            ->willReturn(3)
        ;
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('political_committee.membership.has_max_number_of_mayor_and_leader', $this->anything())
        ;
        $this->politicalCommitteeManager->createMayorOrLeaderMembership($territorialCouncil, $adherent);
    }

    public function testCreateMayorMembership(): void
    {
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $politicalCommittee = new PoliticalCommittee('Test CoPol', $territorialCouncil);
        $territorialCouncil->setPoliticalCommittee($politicalCommittee);
        $adherent = $this->createAdherent();
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));

        $this->membershipRepository
            ->expects($this->once())
            ->method('countLeaderAndMayorMembersFor')
            ->with($politicalCommittee)
            ->willReturn(1)
        ;
        $this->mandateRepository
            ->expects($this->once())
            ->method('hasMayorMandate')
            ->with($adherent)
            ->willReturn(true)
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(PoliticalCommitteeMembership::class))
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('flush')
        ;
        $this->politicalCommitteeManager->createMayorOrLeaderMembership($territorialCouncil, $adherent);

        $this->assertNotNull($adherent->getPoliticalCommitteeMembership());
        $this->assertTrue($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::MAYOR));
        $this->assertFalse($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::LEADER));
    }

    public function testCreateLeaderMembership(): void
    {
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $politicalCommittee = new PoliticalCommittee('Test CoPol', $territorialCouncil);
        $territorialCouncil->setPoliticalCommittee($politicalCommittee);
        $adherent = $this->createAdherent();
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));

        $this->membershipRepository
            ->expects($this->once())
            ->method('countLeaderAndMayorMembersFor')
            ->with($politicalCommittee)
            ->willReturn(1)
        ;
        $this->mandateRepository
            ->expects($this->once())
            ->method('hasMayorMandate')
            ->with($adherent)
            ->willReturn(false)
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(PoliticalCommitteeMembership::class))
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('flush')
        ;
        $this->politicalCommitteeManager->createMayorOrLeaderMembership($territorialCouncil, $adherent);

        $this->assertNotNull($adherent->getPoliticalCommitteeMembership());
        $this->assertTrue($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::LEADER));
        $this->assertFalse($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::MAYOR));
    }

    /**
     * @expectedException \App\TerritorialCouncil\Exception\PoliticalCommitteeMembershipException
     */
    public function testCannotRemoveMayorOrLeaderMembershipIfNoTerritorialCouncil(): void
    {
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $adherent = $this->createAdherent();

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('territorial_council.adherent_has_no_membership', $this->anything())
        ;
        $this->politicalCommitteeManager->removeMayorOrLeaderMembership($territorialCouncil, $adherent);
    }

    /**
     * @expectedException \App\TerritorialCouncil\Exception\PoliticalCommitteeMembershipException
     */
    public function testCannotRemoveMayorOrLeaderMembershipIfNoPoliticalCommitteeMembership(): void
    {
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $adherent = $this->createAdherent();
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('political_committee.membership.adherent_has_no_membership', $this->anything())
        ;
        $this->politicalCommitteeManager->removeMayorOrLeaderMembership($territorialCouncil, $adherent);
    }

    public function testRemoveMayorMembership(): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $pcMembership->addQuality(new PoliticalCommitteeQuality(TerritorialCouncilQualityEnum::MAYOR));
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $adherent = $this->createAdherent();
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));
        $adherent->setPoliticalCommitteeMembership($pcMembership);

        $this->assertTrue($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::MAYOR));

        $this->mandateRepository
            ->expects($this->once())
            ->method('hasMayorMandate')
            ->with($adherent)
            ->willReturn(true)
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('flush')
        ;
        $this->politicalCommitteeManager->removeMayorOrLeaderMembership($territorialCouncil, $adherent);

        $this->assertFalse($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::MAYOR));
    }

    public function testRemoveLeaderMembership(): void
    {
        $pcMembership = new PoliticalCommitteeMembership(new PoliticalCommittee('PC name', new TerritorialCouncil()));
        $pcMembership->addQuality(new PoliticalCommitteeQuality(TerritorialCouncilQualityEnum::LEADER));
        $territorialCouncil = new TerritorialCouncil('Test TC', '999');
        $adherent = $this->createAdherent();
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership($territorialCouncil));
        $adherent->setPoliticalCommitteeMembership($pcMembership);

        $this->assertTrue($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::LEADER));

        $this->mandateRepository
            ->expects($this->once())
            ->method('hasMayorMandate')
            ->with($adherent)
            ->willReturn(false)
        ;
        $this->entityManager
            ->expects($this->once())
            ->method('flush')
        ;
        $this->politicalCommitteeManager->removeMayorOrLeaderMembership($territorialCouncil, $adherent);

        $this->assertFalse($adherent->getPoliticalCommitteeMembership()->hasQuality(TerritorialCouncilQualityEnum::LEADER));
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

    public function provideNotPassingQualities(): iterable
    {
        yield [TerritorialCouncilQualityEnum::MAYOR];
        yield [TerritorialCouncilQualityEnum::LEADER];
    }

    public function provideElectedMemberQualities(): iterable
    {
        yield [TerritorialCouncilQualityEnum::REGIONAL_COUNCILOR];
        yield [TerritorialCouncilQualityEnum::DEPARTMENT_COUNCILOR];
        yield [TerritorialCouncilQualityEnum::CITY_COUNCILOR];
        yield [TerritorialCouncilQualityEnum::CONSULAR_CONSELOR];
        yield [TerritorialCouncilQualityEnum::COMMITTEE_SUPERVISOR];
        yield [TerritorialCouncilQualityEnum::ELECTED_CANDIDATE_ADHERENT];
    }

    private function createAdherent(): Adherent
    {
        return $adherent = Adherent::create(
            Uuid::fromString('c0d66d5f-e124-4641-8fd1-1dd72ffda563'),
            'd.dupont@test.com',
            'password',
            Genders::MALE,
            'Damien',
            'DUPONT',
            new \DateTime('1979-03-25'),
            'position',
            PostAddress::createFrenchAddress('2 Rue de la République', '69001-69381')
        );
    }
}

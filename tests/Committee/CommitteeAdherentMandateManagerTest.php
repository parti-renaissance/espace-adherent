<?php

namespace Tests\App\Committee;

use App\Committee\CommitteeAdherentMandateManager;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\BaseGroup;
use App\Entity\Committee;
use App\Entity\PostAddress;
use App\Entity\TerritorialCouncil\TerritorialCouncilMembership;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @group committee
 */
class CommitteeAdherentMandateManagerTest extends TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject|EntityManagerInterface */
    private $entityManager;
    /** @var \PHPUnit_Framework_MockObject_MockObject|CommitteeAdherentMandateRepository */
    private $mandateRepository;
    /** @var \PHPUnit_Framework_MockObject_MockObject|TranslatorInterface */
    private $translator;

    public function setUp()
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mandateRepository = $this->createMock(CommitteeAdherentMandateRepository::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
    }

    public function tearDown()
    {
        $this->entityManager = null;
        $this->mandateRepository = null;
        $this->translator = null;
    }

    /**
     * @expectedException \App\Committee\Exception\CommitteeAdherentMandateException
     */
    public function testCannotCreateMandateIfIncorrectGender()
    {
        $adherent = $this->createAdherent(Genders::OTHER);
        $committee = $this->createCommittee();

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.not_valid_gender', $this->anything())
        ;
        $politicalCommitteeManager = new CommitteeAdherentMandateManager($this->entityManager, $this->mandateRepository, $this->translator);

        $politicalCommitteeManager->createMandate($adherent, $committee);
    }

    /**
     * @expectedException \App\Committee\Exception\CommitteeAdherentMandateException
     */
    public function testCannotCreateMandateIfAdherentHasActiveMandate()
    {
        $activeMandate = new CommitteeAdherentMandate(
            $this->createAdherent(),
            Genders::FEMALE,
            $this->createCommittee(),
            new \DateTime()
        );

        $adherent = $this->createAdherent(Genders::FEMALE);
        $committee = $this->createCommittee();
        $mandate = new CommitteeAdherentMandate(
            new Adherent(),
            Genders::FEMALE,
            $this->createCommittee(),
            new \DateTime()
        );
        $mandate->setBeginAt(new \DateTime('2020-07-07'));

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.adherent_with_active_mandate', $this->anything())
        ;
        $this->mandateRepository
            ->expects($this->once())
            ->method('findActiveMandate')
            ->with($adherent, $committee)
            ->willReturn($activeMandate)
        ;

        $politicalCommitteeManager = new CommitteeAdherentMandateManager($this->entityManager, $this->mandateRepository, $this->translator);

        $politicalCommitteeManager->createMandate($adherent, $committee);
    }

    /**
     * @expectedException \App\Committee\Exception\CommitteeAdherentMandateException
     */
    public function testCannotCreateMandateIfAdherentIsMemberOfTerritorialCouncil()
    {
        $adherent = $this->createAdherent(Genders::FEMALE);
        $adherent->setTerritorialCouncilMembership(new TerritorialCouncilMembership());
        $committee = $this->createCommittee();

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.adherent_has_territorial_council_membership', $this->anything())
        ;
        $this->mandateRepository
            ->expects($this->once())
            ->method('findActiveMandate')
            ->with($adherent, $committee)
            ->willReturn(null)
        ;

        $politicalCommitteeManager = new CommitteeAdherentMandateManager($this->entityManager, $this->mandateRepository, $this->translator);

        $politicalCommitteeManager->createMandate($adherent, $committee);
    }

    /**
     * @dataProvider provideGenders
     *
     * @expectedException \App\Committee\Exception\CommitteeAdherentMandateException
     */
    public function testCannotCreateMandateIfCommitteeHasActiveMandate(string $gender)
    {
        $adherent = $this->createAdherent($gender);
        $committee = $this->createCommittee();
        $mandate = new CommitteeAdherentMandate(new Adherent(), $gender, $committee, new \DateTime());
        $mandate->setBeginAt(new \DateTime('2020-07-07'));
        $committee->addAdherentMandate($mandate);

        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.committee_has_already_active_mandate', $this->anything())
        ;
        $politicalCommitteeManager = new CommitteeAdherentMandateManager($this->entityManager, $this->mandateRepository, $this->translator);

        $politicalCommitteeManager->createMandate($adherent, $committee);
    }

    /**
     * @dataProvider provideGenders
     */
    public function testCreateMandate(string $gender)
    {
        $adherent = $this->createAdherent($gender);
        $committee = $this->createCommittee();

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(CommitteeAdherentMandate::class))
        ;
        $this->entityManager->expects($this->once())->method('flush');

        $politicalCommitteeManager = new CommitteeAdherentMandateManager($this->entityManager, $this->mandateRepository, $this->translator);

        $politicalCommitteeManager->createMandate($adherent, $committee);

        $this->assertCount(1, $committee->getAdherentMandates());
        $this->assertInstanceOf(CommitteeAdherentMandate::class, $committee->getAdherentMandates()->first());
    }

    /**
     * @dataProvider provideGenders
     *
     * @expectedException \App\Committee\Exception\CommitteeAdherentMandateException
     */
    public function testCannotEndMandateBecauseMandateNotFound(string $gender)
    {
        $adherent = $this->createAdherent($gender);
        $committee = $this->createCommittee();

        $this->mandateRepository
            ->expects($this->once())
            ->method('findActiveMandateFor')
            ->with($adherent, $committee)
            ->willReturn(null)
        ;

        $politicalCommitteeManager = new CommitteeAdherentMandateManager($this->entityManager, $this->mandateRepository, $this->translator);

        $politicalCommitteeManager->endMandate($adherent, $committee);
    }

    /**
     * @dataProvider provideGenders
     */
    public function testEndMandate(string $gender)
    {
        $adherent = $this->createAdherent($gender);
        $committee = $this->createCommittee();
        $mandate = new CommitteeAdherentMandate($adherent, $gender, $committee, new \DateTime('2020-08-26 10:10:10'));

        $this->assertNull($mandate->getFinishAt());

        $this->mandateRepository
            ->expects($this->once())
            ->method('findActiveMandateFor')
            ->with($adherent, $committee)
            ->willReturn($mandate)
        ;

        $politicalCommitteeManager = new CommitteeAdherentMandateManager($this->entityManager, $this->mandateRepository, $this->translator);

        $politicalCommitteeManager->endMandate($adherent, $committee);

        $this->assertNotNull($mandate->getFinishAt());
    }

    private function createAdherent(string $gender = Genders::MALE): Adherent
    {
        return $adherent = Adherent::create(
            Uuid::fromString('c0d66d5f-e124-4641-8fd1-1dd72ffda563'),
            'd.dupont@test.com',
            'password',
            $gender,
            'Damien',
            'DUPONT',
            new \DateTime('1979-03-25'),
            'position',
            PostAddress::createFrenchAddress('2 Rue de la République', '69001-69381')
        );
    }

    private function createCommittee(): Committee
    {
        return new Committee(
            Uuid::fromString('30619ef2-cc3c-491e-9449-f795ef109898'),
            Uuid::fromString('d3522426-1bac-4da4-ade8-5204c9e2caae'),
            'En Marche ! - Lyon 1',
            'Le comité En Marche ! de Lyon village',
            PostAddress::createFrenchAddress('50 Rue de la Villette', '69003-69383'),
            (new PhoneNumber())->setCountryCode('FR')->setNationalNumber('0407080502'),
            '69003-en-marche-lyon',
            BaseGroup::APPROVED
        );
    }

    public function provideGenders(): iterable
    {
        yield [Genders::MALE];
        yield [Genders::FEMALE];
    }
}

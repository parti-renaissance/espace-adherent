<?php

declare(strict_types=1);

namespace Tests\App\Committee;

use App\Committee\CommitteeAdherentMandateManager;
use App\Committee\CommitteeMembershipManager;
use App\Committee\DTO\CommitteeAdherentMandateCommand;
use App\Committee\Exception\CommitteeAdherentMandateException;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\AdherentMandateInterface;
use App\Entity\AdherentMandate\CommitteeAdherentMandate;
use App\Entity\AdherentMandate\CommitteeMandateQualityEnum;
use App\Entity\Committee;
use App\Repository\AdherentMandate\CommitteeAdherentMandateRepository;
use App\Repository\ElectedRepresentative\ElectedRepresentativeRepository;
use App\ValueObject\Genders;
use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Uid\Uuid;
use Symfony\Contracts\Translation\TranslatorInterface;
use Tests\App\AbstractKernelTestCase;

#[Group('committee')]
class CommitteeAdherentMandateManagerTest extends AbstractKernelTestCase
{
    public function testCannotCreateMandateIfIncorrectGender(): void
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $adherent = $this->createNewAdherent(Genders::OTHER);
        $committee = $this->createCommittee();

        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.not_valid_gender', $this->anything())
        ;

        $this->createManager(translator: $translator)->createMandate($adherent, $committee);
    }

    public function testCannotCreateMandateIfAdherentHasActiveMandate(): void
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $activeMandate = new CommitteeAdherentMandate(
            $this->createNewAdherent(),
            Genders::FEMALE,
            new \DateTime()
        );
        $activeMandate->setCommittee($this->createCommittee());

        $adherent = $this->createNewAdherent(Genders::FEMALE);
        $committee = $this->createCommittee();
        $mandate = new CommitteeAdherentMandate(
            new Adherent(),
            Genders::FEMALE,
            new \DateTime('2020-07-07')
        );
        $mandate->setCommittee($this->createCommittee());

        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.adherent_with_active_mandate', $this->anything())
        ;
        $mandateRepository = $this->createMock(CommitteeAdherentMandateRepository::class);
        $mandateRepository
            ->expects($this->once())
            ->method('findActiveMandate')
            ->with($adherent, $committee)
            ->willReturn($activeMandate)
        ;

        $this->createManager(mandateRepository: $mandateRepository, translator: $translator)
            ->createMandate($adherent, $committee)
        ;
    }

    #[DataProvider('provideGenders')]
    public function testCannotCreateMandateIfCommitteeHasActiveMandate(string $gender): void
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $adherent = $this->createNewAdherent($gender);
        $committee = $this->createCommittee();
        $mandate = new CommitteeAdherentMandate(new Adherent(), $gender, new \DateTime('2020-07-07'));
        $mandate->setCommittee($committee);
        $committee->addAdherentMandate($mandate);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.committee_has_already_active_mandate', $this->anything())
        ;

        $this->createManager(translator: $translator)->createMandate($adherent, $committee);
    }

    #[DataProvider('provideGenders')]
    public function testCreateMandate(string $gender): void
    {
        $adherent = $this->createNewAdherent($gender);
        $committee = $this->createCommittee();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(CommitteeAdherentMandate::class))
        ;
        $entityManager->expects($this->once())->method('flush');

        $this->createManager(entityManager: $entityManager)->createMandate($adherent, $committee);

        $this->assertCount(1, $committee->getAdherentMandates());
        $this->assertInstanceOf(CommitteeAdherentMandate::class, $committee->getAdherentMandates()->first());
    }

    #[DataProvider('provideGenders')]
    public function testCannotEndMandateBecauseMandateNotFound(string $gender): void
    {
        $this->expectException(CommitteeAdherentMandateException::class);
        $adherent = $this->createNewAdherent($gender);
        $committee = $this->createCommittee();

        $mandateRepository = $this->createMock(CommitteeAdherentMandateRepository::class);
        $mandateRepository
            ->expects($this->once())
            ->method('findActiveMandateFor')
            ->with($adherent, $committee)
            ->willReturn(null)
        ;

        $this->createManager(mandateRepository: $mandateRepository)->endMandate($adherent, $committee);
    }

    #[DataProvider('provideGenders')]
    public function testEndMandate(string $gender): void
    {
        $adherent = $this->createNewAdherent($gender);
        $committee = $this->createCommittee();
        $mandate = new CommitteeAdherentMandate($adherent, $gender, new \DateTime('2020-08-26 10:10:10'));
        $mandate->setCommittee($committee);

        $this->assertNull($mandate->getFinishAt());

        $mandateRepository = $this->createMock(CommitteeAdherentMandateRepository::class);
        $mandateRepository
            ->expects($this->once())
            ->method('findActiveMandateFor')
            ->with($adherent, $committee)
            ->willReturn($mandate)
        ;

        $this->createManager(mandateRepository: $mandateRepository)->endMandate($adherent, $committee);

        $this->assertNotNull($mandate->getFinishAt());
    }

    public function testCheckAdherentForMandateReplacementFailsIfAdherentHasInappropriateGender(): void
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $adherent = $this->createNewAdherent(Genders::MALE);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.inappropriate_gender')
        ;

        $this->createManager(translator: $translator)
            ->checkAdherentForMandateReplacement($adherent, Genders::FEMALE)
        ;
    }

    public function testCheckAdherentForMandateReplacementFailsIfAdherentMinor(): void
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $adherent = $this->createNewAdherent(Genders::MALE, new \DateTime()->modify('-17 years')->format('Y-m-d'));

        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.adherent.not_valid')
        ;

        $this->createManager(translator: $translator)
            ->checkAdherentForMandateReplacement($adherent, Genders::MALE)
        ;
    }

    public function testCheckAdherentForMandateReplacementFailsIfAdherentHasActiveParliamentaryMandate(): void
    {
        $this->expectException(CommitteeAdherentMandateException::class);

        $adherent = $this->createNewAdherent(Genders::MALE);

        $electedRepresentativeRepository = $this->createMock(ElectedRepresentativeRepository::class);
        $electedRepresentativeRepository
            ->expects($this->once())
            ->method('hasActiveParliamentaryMandate')
            ->with($adherent)
            ->willReturn(true)
        ;
        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects($this->once())
            ->method('trans')
            ->with('adherent_mandate.committee.adherent.not_valid')
        ;

        $this->createManager(
            electedRepresentativeRepository: $electedRepresentativeRepository,
            translator: $translator,
        )->checkAdherentForMandateReplacement($adherent, Genders::MALE);
    }

    public function testCanReplaceMandate(): void
    {
        $committee = $this->createCommittee();
        $adherent = $this->createNewAdherent(Genders::MALE);
        $mandate = $this->createMandate(Genders::MALE, $committee);
        $command = new CommitteeAdherentMandateCommand($committee);
        $command->setGender($mandate->getGender());
        $command->setAdherent($adherent);
        $command->setProvisional(false);

        $translator = $this->createMock(TranslatorInterface::class);
        $translator
            ->expects($this->never())
            ->method('trans')
        ;
        $committeeManager = $this->createMock(CommitteeMembershipManager::class);
        $committeeManager
            ->expects($this->once())
            ->method('followCommittee')
            ->with($adherent, $committee)
        ;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(CommitteeAdherentMandate::class))
        ;
        $entityManager
            ->expects($this->once())
            ->method('flush')
        ;

        $this->createManager(
            entityManager: $entityManager,
            committeeManager: $committeeManager,
            translator: $translator,
        )->replaceMandate($mandate, $command);

        $this->assertNotNull($mandate->getFinishAt());
        $this->assertSame(AdherentMandateInterface::REASON_REPLACED, $mandate->getReason());
    }

    public function testCanCreateMandateFromCommand(): void
    {
        $committee = $this->createCommittee();
        $adherent = $this->createNewAdherent(Genders::MALE);
        $mandateCommand = new CommitteeAdherentMandateCommand($committee);
        $mandateCommand->setAdherent($adherent);
        $mandateCommand->setGender($gender = Genders::MALE);
        $mandateCommand->setQuality($quality = CommitteeMandateQualityEnum::SUPERVISOR);
        $mandateCommand->setProvisional($isProvisional = true);

        $committeeManager = $this->createMock(CommitteeMembershipManager::class);
        $committeeManager
            ->expects($this->once())
            ->method('followCommittee')
            ->with($adherent, $committee)
        ;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(CommitteeAdherentMandate::class))
        ;
        $entityManager
            ->expects($this->once())
            ->method('flush')
        ;

        $mandate = $this->createManager(
            entityManager: $entityManager,
            committeeManager: $committeeManager,
        )->createMandateFromCommand($mandateCommand);

        $this->assertSame($committee, $mandate->getCommittee());
        $this->assertSame($adherent, $mandate->getAdherent());
        $this->assertSame($gender, $mandate->getGender());
        $this->assertSame($quality, $mandate->getQuality());
        $this->assertSame($isProvisional, $mandate->isProvisional());
        $this->assertSame(new \DateTime()->format('Y/m/d'), $mandate->getBeginAt()->format('Y/m/d'));
        $this->assertNull($mandate->getFinishAt());
        $this->assertNull($mandate->getReason());
    }

    private function createManager(
        ?EntityManagerInterface $entityManager = null,
        ?CommitteeAdherentMandateRepository $mandateRepository = null,
        ?ElectedRepresentativeRepository $electedRepresentativeRepository = null,
        ?CommitteeMembershipManager $committeeManager = null,
        ?TranslatorInterface $translator = null,
    ): CommitteeAdherentMandateManager {
        return new CommitteeAdherentMandateManager(
            $entityManager ?? $this->createStub(EntityManagerInterface::class),
            $mandateRepository ?? $this->createStub(CommitteeAdherentMandateRepository::class),
            $electedRepresentativeRepository ?? $this->createStub(ElectedRepresentativeRepository::class),
            $committeeManager ?? $this->createStub(CommitteeMembershipManager::class),
            $translator ?? $this->createStub(TranslatorInterface::class),
        );
    }

    private function createNewAdherent(string $gender = Genders::MALE, ?string $birthday = null): Adherent
    {
        return Adherent::create(
            Uuid::fromString('c0d66d5f-e124-4641-8fd1-1dd72ffda563'),
            'ABC-234',
            'd.dupont@test.com',
            'password',
            $gender,
            'Damien',
            'DUPONT',
            new \DateTime($birthday ?: '1979-03-25'),
            'position',
            $this->createPostAddress('2 Rue de la République', '69001-69381')
        );
    }

    private function createCommittee(): Committee
    {
        return new Committee(
            Uuid::fromString('30619ef2-cc3c-491e-9449-f795ef109898'),
            Uuid::fromString('d3522426-1bac-4da4-ade8-5204c9e2caae'),
            'En Marche ! - Lyon 1',
            'Le comité En Marche ! de Lyon village',
            $this->createPostAddress('50 Rue de la Villette', '69003-69383'),
            new PhoneNumber()->setCountryCode('FR')->setNationalNumber('0407080502'),
            '69003-en-marche-lyon',
        );
    }

    private function createMandate(string $gender, ?Committee $committee = null): CommitteeAdherentMandate
    {
        return CommitteeAdherentMandate::createForCommittee(
            $committee ?? $this->createCommittee(),
            $this->createNewAdherent($gender)
        );
    }

    public static function provideGenders(): iterable
    {
        yield [Genders::MALE];
        yield [Genders::FEMALE];
    }
}

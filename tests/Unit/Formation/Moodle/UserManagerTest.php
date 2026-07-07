<?php

declare(strict_types=1);

namespace Tests\App\Unit\Formation\Moodle;

use App\Entity\Adherent;
use App\Entity\Geo\Zone;
use App\Entity\Moodle\User;
use App\Formation\Moodle\Driver;
use App\Formation\Moodle\UserManager;
use App\Repository\AdherentRepository;
use App\Repository\Moodle\MoodleUserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class UserManagerTest extends TestCase
{
    private const string UUID = 'a0d50e4c-3279-4b3a-9f1e-6b9d2c8f0001';
    private const string NEW_EMAIL = 'new@example.com';

    public function testUpdateUserPushesEmailChangeToMoodleInsteadOfCreatingDuplicate(): void
    {
        $adherent = $this->createAdherentStub(self::NEW_EMAIL);
        $moodleUser = new User($adherent, 100);

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository->expects(self::once())->method('findOneByUuid')->with(self::UUID)->willReturn($adherent);

        $moodleUserRepository = $this->createMock(MoodleUserRepository::class);
        $moodleUserRepository->expects(self::once())->method('findOneBy')->with(['adherent' => $adherent])->willReturn($moodleUser);

        $driver = $this->createMock(Driver::class);
        // Name already matches on Moodle side, so only the email change is pushed.
        $driver->expects(self::once())->method('findUserById')->with(100)->willReturn(['id' => 100, 'email' => 'old@example.com', 'firstname' => 'Jean', 'lastname' => 'Dupont']);
        $driver->expects(self::once())->method('updateUser')->with(100, ['email' => self::NEW_EMAIL, 'username' => self::NEW_EMAIL]);
        $driver->expects(self::never())->method('createUser');
        $driver->expects(self::never())->method('findUserByEmail');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('persist');

        $manager = new UserManager($adherentRepository, $moodleUserRepository, $entityManager, $driver, new NullLogger());
        $manager->updateUser(self::UUID);
    }

    public function testUpdateUserDoesNotTouchMoodleWhenEmailUnchanged(): void
    {
        $adherent = $this->createAdherentStub(self::NEW_EMAIL);
        $moodleUser = new User($adherent, 100);

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository->expects(self::once())->method('findOneByUuid')->with(self::UUID)->willReturn($adherent);

        $moodleUserRepository = $this->createMock(MoodleUserRepository::class);
        $moodleUserRepository->expects(self::once())->method('findOneBy')->with(['adherent' => $adherent])->willReturn($moodleUser);

        $driver = $this->createMock(Driver::class);
        // Email and name all match on Moodle side, so nothing is pushed.
        $driver->expects(self::once())->method('findUserById')->with(100)->willReturn(['id' => 100, 'email' => self::NEW_EMAIL, 'firstname' => 'Jean', 'lastname' => 'Dupont']);
        $driver->expects(self::never())->method('updateUser');
        $driver->expects(self::never())->method('createUser');

        $entityManager = $this->createStub(EntityManagerInterface::class);

        $manager = new UserManager($adherentRepository, $moodleUserRepository, $entityManager, $driver, new NullLogger());
        $manager->updateUser(self::UUID);
    }

    public function testUpdateUserCreatesMoodleAccountWhenAdherentHasNoLink(): void
    {
        $adherent = $this->createAdherentStub(self::NEW_EMAIL);

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository->expects(self::once())->method('findOneByUuid')->with(self::UUID)->willReturn($adherent);

        $moodleUserRepository = $this->createMock(MoodleUserRepository::class);
        $moodleUserRepository->expects(self::once())->method('findOneBy')->with(['adherent' => $adherent])->willReturn(null);

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::once())->method('findUserByEmail')->with(self::NEW_EMAIL)->willReturn([]);
        $driver->expects(self::once())->method('createUser')->willReturn(['id' => 500]);
        $driver->expects(self::never())->method('updateUser');
        $driver->expects(self::never())->method('findUserById');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(User::class));

        $manager = new UserManager($adherentRepository, $moodleUserRepository, $entityManager, $driver, new NullLogger());
        $manager->updateUser(self::UUID);
    }

    public function testCreateUserIncludesProfileFieldsFromAddress(): void
    {
        // Lowercase country to prove it is normalised to the ISO-3166 uppercase code Moodle expects.
        $adherent = $this->createAdherentStub(self::NEW_EMAIL, 'fr', 'Paris', '75', 'Paris');

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository->expects(self::once())->method('findOneByUuid')->with(self::UUID)->willReturn($adherent);

        $moodleUserRepository = $this->createMock(MoodleUserRepository::class);
        $moodleUserRepository->expects(self::once())->method('findOneBy')->with(['adherent' => $adherent])->willReturn(null);

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::once())->method('findUserByEmail')->with(self::NEW_EMAIL)->willReturn([]);
        $driver->expects(self::once())->method('createUser')->with([
            'email' => self::NEW_EMAIL,
            'username' => self::NEW_EMAIL,
            'firstname' => 'Jean',
            'lastname' => 'Dupont',
            'auth' => 'oauth2',
            'country' => 'FR',
            'city' => 'Paris',
            'department' => 'Paris (75)',
        ])->willReturn(['id' => 500]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(User::class));

        $manager = new UserManager($adherentRepository, $moodleUserRepository, $entityManager, $driver, new NullLogger());
        $manager->updateUser(self::UUID);
    }

    public function testUpdateUserPushesOnlyChangedProfileFields(): void
    {
        $adherent = $this->createAdherentStub(self::NEW_EMAIL, 'FR', 'Lyon', '69', 'Rhône');
        $moodleUser = new User($adherent, 100);

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository->expects(self::once())->method('findOneByUuid')->with(self::UUID)->willReturn($adherent);

        $moodleUserRepository = $this->createMock(MoodleUserRepository::class);
        $moodleUserRepository->expects(self::once())->method('findOneBy')->with(['adherent' => $adherent])->willReturn($moodleUser);

        $driver = $this->createMock(Driver::class);
        // Same email, name and country, but city and department drifted: only those two are pushed.
        $driver->expects(self::once())->method('findUserById')->with(100)->willReturn([
            'id' => 100,
            'email' => self::NEW_EMAIL,
            'firstname' => 'Jean',
            'lastname' => 'Dupont',
            'country' => 'FR',
            'city' => 'Paris',
            'department' => 'Paris (75)',
        ]);
        $driver->expects(self::once())->method('updateUser')->with(100, ['city' => 'Lyon', 'department' => 'Rhône (69)']);
        $driver->expects(self::never())->method('createUser');

        $entityManager = $this->createStub(EntityManagerInterface::class);

        $manager = new UserManager($adherentRepository, $moodleUserRepository, $entityManager, $driver, new NullLogger());
        $manager->updateUser(self::UUID);
    }

    public function testUpdateUserPushesNameChange(): void
    {
        $adherent = $this->createAdherentStub(self::NEW_EMAIL, firstName: 'Marie', lastName: 'Martin');
        $moodleUser = new User($adherent, 100);

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository->expects(self::once())->method('findOneByUuid')->with(self::UUID)->willReturn($adherent);

        $moodleUserRepository = $this->createMock(MoodleUserRepository::class);
        $moodleUserRepository->expects(self::once())->method('findOneBy')->with(['adherent' => $adherent])->willReturn($moodleUser);

        $driver = $this->createMock(Driver::class);
        // Email unchanged but the name was edited on the adherent: the new name is pushed to Moodle.
        $driver->expects(self::once())->method('findUserById')->with(100)->willReturn([
            'id' => 100,
            'email' => self::NEW_EMAIL,
            'firstname' => 'Jean',
            'lastname' => 'Dupont',
        ]);
        $driver->expects(self::once())->method('updateUser')->with(100, ['firstname' => 'Marie', 'lastname' => 'Martin']);
        $driver->expects(self::never())->method('createUser');

        $entityManager = $this->createStub(EntityManagerInterface::class);

        $manager = new UserManager($adherentRepository, $moodleUserRepository, $entityManager, $driver, new NullLogger());
        $manager->updateUser(self::UUID);
    }

    public function testForeignResidentIsGroupedUnderFdeDepartment(): void
    {
        // A resident abroad resolves to the "FDE" assembly zone, pushed as "<name> (FDE)".
        $adherent = $this->createAdherentStub(self::NEW_EMAIL, 'BE', 'Bruxelles', 'FDE', 'Français de l\'étranger');

        $adherentRepository = $this->createMock(AdherentRepository::class);
        $adherentRepository->expects(self::once())->method('findOneByUuid')->with(self::UUID)->willReturn($adherent);

        $moodleUserRepository = $this->createMock(MoodleUserRepository::class);
        $moodleUserRepository->expects(self::once())->method('findOneBy')->with(['adherent' => $adherent])->willReturn(null);

        $driver = $this->createMock(Driver::class);
        $driver->expects(self::once())->method('findUserByEmail')->with(self::NEW_EMAIL)->willReturn([]);
        $driver->expects(self::once())->method('createUser')->with([
            'email' => self::NEW_EMAIL,
            'username' => self::NEW_EMAIL,
            'firstname' => 'Jean',
            'lastname' => 'Dupont',
            'auth' => 'oauth2',
            'country' => 'BE',
            'city' => 'Bruxelles',
            'department' => 'Français de l\'étranger (FDE)',
        ])->willReturn(['id' => 500]);

        $entityManager = $this->createStub(EntityManagerInterface::class);

        $manager = new UserManager($adherentRepository, $moodleUserRepository, $entityManager, $driver, new NullLogger());
        $manager->updateUser(self::UUID);
    }

    private function createAdherentStub(
        string $email,
        ?string $country = null,
        ?string $cityName = null,
        ?string $assemblyZoneCode = null,
        ?string $assemblyZoneName = null,
        string $firstName = 'Jean',
        string $lastName = 'Dupont',
    ): Adherent {
        $adherent = $this->createStub(Adherent::class);
        $adherent->method('getEmailAddress')->willReturn($email);
        $adherent->method('getId')->willReturn(1);
        $adherent->method('getFirstName')->willReturn($firstName);
        $adherent->method('getLastName')->willReturn($lastName);
        $adherent->method('getCountry')->willReturn($country);
        $adherent->method('getCityName')->willReturn($cityName);

        if (null !== $assemblyZoneCode) {
            $zone = $this->createStub(Zone::class);
            $zone->method('getCode')->willReturn($assemblyZoneCode);
            $zone->method('getName')->willReturn((string) $assemblyZoneName);
            $adherent->method('getAssemblyZone')->willReturn($zone);
        } else {
            // Neutralise the job-reconciliation path: no zones, roles or memberships.
            $adherent->method('getAssemblyZone')->willReturn(null);
        }

        $adherent->method('isRenaissanceAdherent')->willReturn(false);
        $adherent->method('getZoneBasedRoles')->willReturn([]);
        $adherent->method('getAnimatorCommittees')->willReturn([]);
        $adherent->method('getReceivedDelegatedAccesses')->willReturn([]);
        $adherent->method('getStaticLabels')->willReturn(new ArrayCollection());
        $adherent->agoraMemberships = new ArrayCollection();

        return $adherent;
    }
}

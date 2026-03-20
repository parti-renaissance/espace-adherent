<?php

declare(strict_types=1);

namespace Tests\App\Repository\Projection;

use App\Adherent\MandateTypeEnum;
use App\Adherent\Tag\TagEnum;
use App\Entity\Geo\Zone;
use App\ManagedUsers\ManagedUsersFilter;
use App\Repository\Geo\ZoneRepository;
use App\Repository\Projection\ManagedUserRepository;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\Persistence\ObjectRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

#[Group('functional')]
#[Group('referent')]
class ManagedUserRepositoryTest extends AbstractKernelTestCase
{
    private ?ManagedUserRepository $managedUserRepository = null;
    private ?ObjectRepository $zoneRepository = null;

    public function testSearch(): void
    {
        $filter = new ManagedUsersFilter(null, [
            $this->zoneRepository->findOneBy(['code' => 'CH', 'type' => Zone::COUNTRY]),
            $this->zoneRepository->findOneBy(['code' => '77', 'type' => Zone::DEPARTMENT]),
        ]);

        $this->assertCount(3, $this->managedUserRepository->searchByFilter($filter));
    }

    #[DataProvider('providesOnlyEmailSubscribers')]
    public function testSearchWithEmailSubscribersInevitably(?bool $onlyEmailSubscribers, int $count): void
    {
        $filter = new ManagedUsersFilter(SubscriptionTypeEnum::REFERENT_EMAIL, [
            $this->zoneRepository->findOneBy(['code' => 'CH', 'type' => Zone::COUNTRY]),
            $this->zoneRepository->findOneBy(['code' => '77', 'type' => Zone::DEPARTMENT]),
        ]);
        $filter->emailSubscription = $onlyEmailSubscribers;

        $this->assertCount($count, $this->managedUserRepository->searchByFilter($filter));
    }

    public static function providesOnlyEmailSubscribers(): \Generator
    {
        yield [null, 3];
        yield [true, 1];
        yield [false, 2];
    }

    public function testIterateForExportReturnsGenerator(): void
    {
        $filter = new ManagedUsersFilter(null, [
            $this->zoneRepository->findOneBy(['code' => 'CH', 'type' => Zone::COUNTRY]),
            $this->zoneRepository->findOneBy(['code' => '77', 'type' => Zone::DEPARTMENT]),
        ]);

        $generator = $this->managedUserRepository->iterateForExport($filter);

        $this->assertInstanceOf(\Generator::class, $generator);

        $results = iterator_to_array($generator);

        $this->assertCount(3, $results);
        $this->assertIsArray($results[0]);
        $this->assertArrayHasKey('firstName', $results[0]);
        $this->assertArrayHasKey('lastName', $results[0]);
        $this->assertArrayHasKey('email', $results[0]);
    }

    public function testIterateForExportWithEmailSubscribersFilter(): void
    {
        $filter = new ManagedUsersFilter(SubscriptionTypeEnum::REFERENT_EMAIL, [
            $this->zoneRepository->findOneBy(['code' => 'CH', 'type' => Zone::COUNTRY]),
            $this->zoneRepository->findOneBy(['code' => '77', 'type' => Zone::DEPARTMENT]),
        ]);
        $filter->emailSubscription = true;

        $results = iterator_to_array($this->managedUserRepository->iterateForExport($filter));

        $this->assertCount(1, $results);
    }

    public function testSearchBySearchTerm(): void
    {
        $filter = $this->createDefaultFilter();
        $filter->searchTerm = 'Michelle';

        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));

        $filter->searchTerm = 'Dufour';
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));

        // Email search
        $filter->searchTerm = 'michelle.dufour@example.ch';
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));

        // Non-matching search
        $filter->searchTerm = 'NonExistentName';
        $this->assertCount(0, $this->managedUserRepository->searchByFilter($filter));
    }

    public function testSearchByFirstName(): void
    {
        $filter = $this->createDefaultFilter();
        $filter->firstName = 'Michel';

        // Should match both "Michelle" (adherent-1) and "Michel" (adherent-13)
        $this->assertCount(2, $this->managedUserRepository->searchByFilter($filter));

        $filter->firstName = 'Francis';
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));
    }

    public function testSearchByLastName(): void
    {
        $filter = $this->createDefaultFilter();
        $filter->lastName = 'Dufour';

        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));

        // Partial match
        $filter->lastName = 'VASSEUR';
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));
    }

    #[DataProvider('provideGenderFilter')]
    public function testSearchByGender(string $gender, int $expectedCount): void
    {
        $filter = $this->createDefaultFilter();
        $filter->gender = $gender;

        $this->assertCount($expectedCount, $this->managedUserRepository->searchByFilter($filter));
    }

    public static function provideGenderFilter(): \Generator
    {
        yield 'female' => ['female', 1]; // Michelle Dufour
        yield 'male' => ['male', 2]; // Michel Vasseur + Francis Brioul
    }

    public function testSearchByNationality(): void
    {
        $filter = $this->createDefaultFilter();
        $filter->nationality = 'FR';

        // All 3 managed users in CH + 77 have FR nationality
        $this->assertCount(3, $this->managedUserRepository->searchByFilter($filter));

        $filter->nationality = 'DE';
        $this->assertCount(0, $this->managedUserRepository->searchByFilter($filter));
    }

    #[DataProvider('provideAgeFilter')]
    public function testSearchByAge(?int $ageMin, ?int $ageMax, int $expectedCount): void
    {
        $filter = $this->createDefaultFilter();
        $filter->ageMin = $ageMin;
        $filter->ageMax = $ageMax;

        $this->assertCount($expectedCount, $this->managedUserRepository->searchByFilter($filter));
    }

    public static function provideAgeFilter(): \Generator
    {
        // Birthdates from fixtures:
        // - adherent-1 (Michelle Dufour): 1972-11-23 -> ~53 years old
        // - adherent-13 (Michel Vasseur): 1987-05-13 -> ~38 years old
        // - adherent-7 (Francis Brioul): 1962-01-07 -> ~64 years old
        yield 'min only - 50' => [50, null, 2]; // Michelle (53) + Francis (64)
        yield 'max only - 45' => [null, 45, 1]; // Michel (38)
        yield 'range 35-55' => [35, 55, 2]; // Michelle (53) + Michel (38)
        yield 'range 60-70' => [60, 70, 1]; // Francis (64)
        yield 'no match' => [18, 25, 0];
    }

    #[DataProvider('provideIsCertifiedFilter')]
    public function testSearchByIsCertified(?bool $isCertified, int $expectedCount): void
    {
        $filter = $this->createDefaultFilter();
        $filter->isCertified = $isCertified;

        $this->assertCount($expectedCount, $this->managedUserRepository->searchByFilter($filter));
    }

    public static function provideIsCertifiedFilter(): \Generator
    {
        // Michelle Dufour and Michel Vasseur are certified, Francis Brioul is not
        yield 'certified only' => [true, 2];
        yield 'not certified only' => [false, 1];
        yield 'no filter' => [null, 3];
    }

    public function testSearchByAdherentTags(): void
    {
        // Tags from actual DB after fixture loading (verified via doctrine:query:sql):
        // - Michelle Dufour: adherent:a_jour_2026:recotisation
        // - Michel VASSEUR: adherent:plus_a_jour:annee_2024, elu:cotisation_ok:soumis
        // - Francis Brioul: adherent:plus_a_jour:annee_2024, elu:cotisation_ok:soumis
        $filter = $this->createDefaultFilter();

        // Test with plus_a_jour pattern - only Michel and Francis have this
        $filter->adherentTags = 'adherent:plus_a_jour';
        $this->assertCount(2, $this->managedUserRepository->searchByFilter($filter));

        // Test with a_jour pattern - Michelle has adherent:a_jour_2026:recotisation which contains 'adherent:a_jour'
        $filter->adherentTags = 'adherent:a_jour';
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));
    }

    public function testSearchByElectTags(): void
    {
        // Tags from actual DB after fixture loading (verified via doctrine:query:sql):
        // - Michelle Dufour: no elect tag (adherent:a_jour_2026:recotisation only)
        // - Michel VASSEUR: elu:cotisation_ok:soumis
        // - Francis Brioul: elu:cotisation_ok:soumis
        $filter = $this->createDefaultFilter();
        $filter->electTags = TagEnum::ELU_COTISATION_OK_SOUMIS;

        // Michel Vasseur and Francis Brioul have elu:cotisation_ok:soumis
        $this->assertCount(2, $this->managedUserRepository->searchByFilter($filter));

        // Nobody in CH+77 zones has elu:cotisation_ok:non_soumis
        $filter->electTags = TagEnum::ELU_COTISATION_OK_NON_SOUMIS;
        $this->assertCount(0, $this->managedUserRepository->searchByFilter($filter));
    }

    public function testSearchByStaticTags(): void
    {
        $filter = $this->createDefaultFilter();
        // national_event:present:congres-2024 is a static tag
        $filter->staticTags = 'national_event:present:congres-2024';

        // Only adherent-7 (Francis Brioul) has this tag
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));
    }

    public function testSearchByStaticTagsNegation(): void
    {
        $filter = $this->createDefaultFilter();
        // Using '--' suffix for negation
        $filter->staticTags = 'national_event:present:congres-2024--';

        // All users except adherent-7 (2 users don't have this tag)
        $this->assertCount(2, $this->managedUserRepository->searchByFilter($filter));
    }

    public function testSearchByDeclaredMandates(): void
    {
        $filter = $this->createDefaultFilter();
        $filter->declaredMandates = [MandateTypeEnum::CONSEILLER_MUNICIPAL];

        // adherent-1 has CONSEILLER_MUNICIPAL declared mandate
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));

        $filter->declaredMandates = [MandateTypeEnum::DEPUTE_EUROPEEN];
        // adherent-13 has DEPUTE_EUROPEEN declared mandate
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));

        // Multiple declared mandates (OR condition)
        $filter->declaredMandates = [MandateTypeEnum::CONSEILLER_MUNICIPAL, MandateTypeEnum::DEPUTE_EUROPEEN];
        $this->assertCount(2, $this->managedUserRepository->searchByFilter($filter));
    }

    public function testSearchByElectMandates(): void
    {
        // In default zones (CH + 77), no one has elect mandates
        $filter = $this->createDefaultFilter();
        $filter->electMandates = [MandateTypeEnum::CONSEILLER_MUNICIPAL];
        $this->assertCount(0, $this->managedUserRepository->searchByFilter($filter));

        // Gisele Berthoux (adherent-5, ManagedUser 3) has CONSEILLER_MUNICIPAL elect mandate
        // and is in zones 92, 92024
        $filter = $this->createHautsDeSeine92Filter();
        $filter->electMandates = [MandateTypeEnum::CONSEILLER_MUNICIPAL];
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));

        // Non-matching mandate type in zone 92
        $filter->electMandates = [MandateTypeEnum::DEPUTE_EUROPEEN];
        $this->assertCount(0, $this->managedUserRepository->searchByFilter($filter));

        // Multiple mandates (OR condition): one matches, one doesn't
        $filter->electMandates = [MandateTypeEnum::CONSEILLER_MUNICIPAL, MandateTypeEnum::SENATEUR];
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));
    }

    public function testSearchByRegisteredSince(): void
    {
        $filter = $this->createDefaultFilter();
        // adherent-7 registered at 2017-01-25
        $filter->registeredSince = new \DateTime('2017-01-20');

        // Should include adherent-7 and adherent-13 (registered after Jan 20)
        // adherent-1: 2017-06-01, adherent-13: 2017-06-02, adherent-7: 2017-01-25
        $this->assertCount(3, $this->managedUserRepository->searchByFilter($filter));

        $filter->registeredSince = new \DateTime('2017-06-01');
        // Only adherent-1 and adherent-13 registered on or after June 1
        $this->assertCount(2, $this->managedUserRepository->searchByFilter($filter));
    }

    public function testSearchByRegisteredUntil(): void
    {
        $filter = $this->createDefaultFilter();
        $filter->registeredUntil = new \DateTime('2017-02-01');

        // Only adherent-7 registered before Feb 1 (2017-01-25)
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));
    }

    public function testSearchByRegisteredRange(): void
    {
        $filter = $this->createDefaultFilter();
        $filter->registeredSince = new \DateTime('2017-01-01');
        $filter->registeredUntil = new \DateTime('2017-05-31');

        // Only adherent-7 in this range (2017-01-25)
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));
    }

    #[DataProvider('provideSmsSubscriptionFilter')]
    public function testSearchBySmsSubscription(?bool $smsSubscription, int $expectedCount): void
    {
        $filter = $this->createDefaultFilter();
        $filter->smsSubscription = $smsSubscription;

        $this->assertCount($expectedCount, $this->managedUserRepository->searchByFilter($filter));
    }

    public static function provideSmsSubscriptionFilter(): \Generator
    {
        // From fixtures:
        // - adherent-1: subscriptionTypes: [MILITANT_ACTION_SMS] -> sms subscribed
        // - adherent-13: subscriptionTypes: [REFERENT_EMAIL, MILITANT_ACTION_SMS] -> sms subscribed
        // - adherent-7: no MILITANT_ACTION_SMS -> not sms subscribed
        yield 'sms subscribed' => [true, 2];
        yield 'sms not subscribed' => [false, 1];
        yield 'no filter' => [null, 3];
    }

    public function testSearchByFirstMembership(): void
    {
        // Use zone 75108 (borough) where ManagedUsers 5 and 6 have cotisation_dates
        // ManagedUser 5 (adherent-3): firstMembershipDonation = 2022-02-01
        // ManagedUser 6 (deputy-75-1): firstMembershipDonation = 2022-01-01
        $filter = $this->createParisFilter();

        // Test firstMembershipSince
        $filter->firstMembershipSince = new \DateTime('2022-01-15');
        // Only adherent-3 has first membership on 2022-02-01 (after Jan 15)
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));

        // Test firstMembershipBefore
        $filter->firstMembershipSince = null;
        $filter->firstMembershipBefore = new \DateTime('2022-01-15');
        // Only deputy-75-1 has first membership on 2022-01-01 (before Jan 15)
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));

        // Test range - both should match between 2021-12-01 and 2022-03-01
        $filter->firstMembershipSince = new \DateTime('2021-12-01');
        $filter->firstMembershipBefore = new \DateTime('2022-03-01');
        $this->assertCount(2, $this->managedUserRepository->searchByFilter($filter));
    }

    public function testSearchByLastMembership(): void
    {
        // Use zone 75108 (borough) where ManagedUsers 5 and 6 have cotisation_dates
        // ManagedUser 5 (adherent-3): lastMembershipDonation = 2023-03-01
        // ManagedUser 6 (deputy-75-1): lastMembershipDonation = 2023-01-01
        $filter = $this->createParisFilter();

        // Test lastMembershipSince
        $filter->lastMembershipSince = new \DateTime('2023-02-01');
        // Only adherent-3 has last membership on 2023-03-01 (after Feb 1)
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));

        // Test lastMembershipBefore
        $filter->lastMembershipSince = null;
        $filter->lastMembershipBefore = new \DateTime('2023-02-01');
        // Only deputy-75-1 has last membership on 2023-01-01 (before Feb 1)
        $this->assertCount(1, $this->managedUserRepository->searchByFilter($filter));

        // Test range - both should match between 2022-12-01 and 2023-04-01
        $filter->lastMembershipSince = new \DateTime('2022-12-01');
        $filter->lastMembershipBefore = new \DateTime('2023-04-01');
        $this->assertCount(2, $this->managedUserRepository->searchByFilter($filter));
    }

    /**
     * Creates a filter with default zones (CH + 77) for consistent testing.
     */
    private function createDefaultFilter(): ManagedUsersFilter
    {
        return new ManagedUsersFilter(null, [
            $this->zoneRepository->findOneBy(['code' => 'CH', 'type' => Zone::COUNTRY]),
            $this->zoneRepository->findOneBy(['code' => '77', 'type' => Zone::DEPARTMENT]),
        ]);
    }

    /**
     * Creates a filter with Paris zone (75108 borough + 75-1 district) for testing cotisation dates.
     * ManagedUsers in this zone: adherent-3 and deputy-75-1 (both have cotisation_dates).
     */
    private function createParisFilter(): ManagedUsersFilter
    {
        return new ManagedUsersFilter(null, [
            $this->zoneRepository->findOneBy(['code' => '75108', 'type' => Zone::BOROUGH]),
            $this->zoneRepository->findOneBy(['code' => '75-1', 'type' => Zone::DISTRICT]),
        ]);
    }

    /**
     * Creates a filter with Hauts-de-Seine zone (92 department).
     * ManagedUser in this zone: Gisele Berthoux (adherent-5, has elect mandate CONSEILLER_MUNICIPAL).
     */
    private function createHautsDeSeine92Filter(): ManagedUsersFilter
    {
        return new ManagedUsersFilter(null, [
            $this->zoneRepository->findOneBy(['code' => '92', 'type' => Zone::DEPARTMENT]),
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->managedUserRepository = $this->get(ManagedUserRepository::class);
        $this->zoneRepository = $this->get(ZoneRepository::class);
    }

    protected function tearDown(): void
    {
        $this->managedUserRepository = null;
        $this->zoneRepository = null;

        parent::tearDown();
    }
}

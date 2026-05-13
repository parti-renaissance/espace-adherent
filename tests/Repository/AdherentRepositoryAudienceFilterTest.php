<?php

declare(strict_types=1);

namespace Tests\App\Repository;

use App\Adherent\MandateTypeEnum;
use App\DataFixtures\ORM\LoadAudienceFilterTestData;
use App\DataFixtures\ORM\LoadCommitteeData;
use App\Donation\DonatorStatusEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMessage\AdherentMessage;
use App\Entity\AdherentMessage\AdherentMessageFilter;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Repository\AdherentRepository;
use App\Scope\ScopeEnum;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractKernelTestCase;

/**
 * Functional tests for AdherentRepository::buildAudienceQueryPieces (Mailchimp email path).
 *
 * The dedicated fixtures (LoadAudienceFilterTestData) create orthogonal adherents,
 * each carrying a single discriminating attribute. Assertions check the inclusion/exclusion
 * of these adherents in the result of findAdherentIdsForMessage().
 *
 * Other fixture adherents (LoadAdherentData) remain in the database; the assertContains
 * and assertNotContains assertions tolerate their presence without enforcing a closed set.
 */
#[Group('functional')]
class AdherentRepositoryAudienceFilterTest extends AbstractKernelTestCase
{
    private ?AdherentRepository $adherentRepository = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->adherentRepository = $this->getAdherentRepository();
    }

    protected function tearDown(): void
    {
        $this->adherentRepository = null;
        parent::tearDown();
    }

    public function testGenderFilterMaleIncludesMaleAdherentAndExcludesFemaleAdherent(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setGender('male');
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FEMALE_OLD), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_GENDER), $ids);
    }

    public function testGenderFilterFemaleIncludesFemaleAdherentAndExcludesMaleAdherent(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setGender('female');
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FEMALE_OLD), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_GENDER), $ids);
    }

    public function testGenderFilterNullDoesNotConstrainOnGender(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setGender(null);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FEMALE_OLD), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_GENDER), $ids);
    }

    public function testAgeMinFilterIncludesAdherentsAtOrAboveTheBound(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setAgeMin(60);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FEMALE_OLD), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testAgeMaxFilterIncludesAdherentsAtOrBelowTheBound(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setAgeMax(30);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FEMALE_OLD), $ids);
    }

    public function testRegisteredSinceFilterIncludesStartOfDay(): void
    {
        // registeredSince = 2023-01-01 must keep everything >= 2023-01-01 00:00:00.
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setRegisteredSince(new \DateTime('2023-01-01'));
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_EDGE_DAY_START), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_EDGE_DAY_END), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_2024), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_2020), $ids);
    }

    public function testRegisteredUntilFilterIncludesEndOfDay(): void
    {
        // registeredUntil = 2023-01-01 must keep everything <= 2023-01-01 23:59:59.
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setRegisteredUntil(new \DateTime('2023-01-01'));
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_EDGE_DAY_START), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_EDGE_DAY_END), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_2020), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_REGISTERED_2024), $ids);
    }

    public function testFirstMembershipSinceFilterIncludesAdherentsWithDonationOnOrAfterTheBound(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->firstMembershipSince = new \DateTime('2022-01-01');
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_MEMBERSHIP_2022), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_LAST_MEMBERSHIP_2025), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_MEMBERSHIP), $ids);
    }

    public function testFirstMembershipBeforeFilterIncludesAdherentsWithDonationOnOrBeforeTheBound(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->firstMembershipBefore = new \DateTime('2021-01-01');
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_LAST_MEMBERSHIP_2025), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_MEMBERSHIP_2022), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_MEMBERSHIP), $ids);
    }

    public function testLastMembershipSinceFilterIncludesAdherentsWithDonationOnOrAfterTheBound(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setLastMembershipSince(new \DateTime('2025-01-01'));
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_LAST_MEMBERSHIP_2025), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_MEMBERSHIP_2022), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_MEMBERSHIP), $ids);
    }

    public function testLastMembershipBeforeFilterIncludesAdherentsWithDonationOnOrBeforeTheBound(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setLastMembershipBefore(new \DateTime('2024-12-31'));
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_MEMBERSHIP_2022), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_LAST_MEMBERSHIP_2025), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_MEMBERSHIP), $ids);
    }

    public function testAdherentTagsIncludeMatchesByPrefix(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            // adherentTags = LIKE prefix sur a.tags
            $filter->adherentTags = 'adherent:renaissance';
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ADHERENT_RENAISSANCE), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ELECT_MAYOR), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_STATIC_NEWSLETTER), $ids);
    }

    public function testAdherentTagsExcludeWithBangPrefix(): void
    {
        // Note: NOT LIKE returns NULL for adherents with tags=NULL — they are therefore
        // excluded from the result. assertContains only targets adherents with non-empty tags.
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->adherentTags = '!adherent:renaissance';
        });

        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ADHERENT_RENAISSANCE), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ELECT_MAYOR), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_STATIC_NEWSLETTER), $ids);
    }

    public function testElectTagsIncludeMatchesByContains(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            // electTags = LIKE contains sur a.tags
            $filter->electTags = 'elect:mayor';
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ELECT_MAYOR), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ADHERENT_RENAISSANCE), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_STATIC_NEWSLETTER), $ids);
    }

    public function testElectTagsExcludeWithBangPrefix(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->electTags = '!elect:mayor';
        });

        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ELECT_MAYOR), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ADHERENT_RENAISSANCE), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_STATIC_NEWSLETTER), $ids);
    }

    public function testStaticTagsIncludeMatchesByContains(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            // staticTags = LIKE contains sur a.tags
            $filter->staticTags = 'newsletter:abonne';
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_STATIC_NEWSLETTER), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ELECT_MAYOR), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ADHERENT_RENAISSANCE), $ids);
    }

    public function testStaticTagsExcludeWithBangPrefix(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->staticTags = '!newsletter:abonne';
        });

        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_STATIC_NEWSLETTER), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ADHERENT_RENAISSANCE), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_TAGS_ELECT_MAYOR), $ids);
    }

    public function testDeclaredMandateIncludeMatchesFindInSet(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setDeclaredMandate('maire');
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_DECLARED_MANDATE_MAIRE), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testDeclaredMandateExcludeWithBangPrefix(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setDeclaredMandate('!maire');
        });

        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_DECLARED_MANDATE_MAIRE), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testIsCertifiedTrueIncludesCertifiedAdherentOnly(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setIsCertified(true);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_CERTIFIED), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testIsCertifiedFalseExcludesCertifiedAdherent(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setIsCertified(false);
        });

        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_CERTIFIED), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testFirstNameFilterMatchesByExactValue(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setFirstName(LoadAudienceFilterTestData::FIRST_NAME_CHARLES);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_NAME_CHARLES), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_LAST_NAME_SPECIAL), $ids);
    }

    public function testLastNameFilterMatchesByExactValue(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setLastName(LoadAudienceFilterTestData::LAST_NAME_SPECIAL);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_LAST_NAME_SPECIAL), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_NAME_CHARLES), $ids);
    }

    public function testDonatorStatusCurrentYearIncludesAdherentsWithDonationThisYear(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setDonatorStatus(DonatorStatusEnum::DONATOR_N);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_DONATOR_CURRENT_YEAR), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_MEMBERSHIP_2022), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_MEMBERSHIP), $ids);
    }

    public function testDonatorStatusNotDonatorIncludesAdherentsWithoutAnyDonation(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setDonatorStatus(DonatorStatusEnum::NOT_DONATOR);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_MEMBERSHIP), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_DONATOR_CURRENT_YEAR), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_MEMBERSHIP_2022), $ids);
    }

    public function testDonatorStatusPastOnlyIncludesAdherentsDonatedBeforeCurrentYear(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setDonatorStatus(DonatorStatusEnum::DONATOR_N_X);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_FIRST_MEMBERSHIP_2022), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_DONATOR_CURRENT_YEAR), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_NO_MEMBERSHIP), $ids);
    }

    public function testElectMandateIncludeReturnsOnlyActiveMandates(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setElectMandate(MandateTypeEnum::CONSEILLER_MUNICIPAL);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ELECT_MANDATE_ACTIVE), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ELECT_MANDATE_PAST), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testElectMandateExcludeRemovesAdherentsWithActiveMandateButKeepsExMandates(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setElectMandate('!'.MandateTypeEnum::CONSEILLER_MUNICIPAL);
        });

        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ELECT_MANDATE_ACTIVE), $ids);
        // Expected semantics: a former representative (ended mandate) remains targetable.
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ELECT_MANDATE_PAST), $ids);
        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testCommitteeFilterIncludesOnlyMembersOfTargetCommittee(): void
    {
        $committee = $this->getCommittee(LoadCommitteeData::COMMITTEE_1_UUID);
        $this->assertInstanceOf(Committee::class, $committee);

        $ids = $this->findIds(static function (AdherentMessageFilter $filter) use ($committee): void {
            $filter->setCommittee($committee);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_COMMITTEE_MEMBER), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testIsCommitteeMemberTrueIncludesAdherentsWithMembership(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setIsCommitteeMember(true);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_COMMITTEE_MEMBER), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    public function testIsCommitteeMemberFalseExcludesAdherentsWithMembership(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->setIsCommitteeMember(false);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_COMMITTEE_MEMBER), $ids);
    }

    public function testZoneFilterCantonIncludesAdherentDirectlyAttached(): void
    {
        $cantonZone = $this->getZoneByCode(LoadAudienceFilterTestData::ZONE_CODE_CANTON);

        $ids = $this->findIds(static function (AdherentMessageFilter $filter) use ($cantonZone): void {
            $filter->setZone($cantonZone);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_CANTON_DIRECT), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    /**
     * City-grouper zones (CANTON, DISTRICT) are assigned directly to adherents — a filter on
     * a CANTON must not cascade to its child communes. Aligns with GeoZoneTrait semantics
     * (DQL push path).
     */
    public function testZoneFilterCantonExcludesCommuneAdherentAttachedOnlyToChild(): void
    {
        $cantonZone = $this->getZoneByCode(LoadAudienceFilterTestData::ZONE_CODE_CANTON);

        $ids = $this->findIds(static function (AdherentMessageFilter $filter) use ($cantonZone): void {
            $filter->setZone($cantonZone);
        });

        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_COMMUNE_OF_CANTON), $ids);
    }

    public function testZoneFilterDistrictIncludesAdherentDirectlyAttached(): void
    {
        $districtZone = $this->getZoneByCode(LoadAudienceFilterTestData::ZONE_CODE_DISTRICT);

        $ids = $this->findIds(static function (AdherentMessageFilter $filter) use ($districtZone): void {
            $filter->setZone($districtZone);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_DISTRICT_DIRECT), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG), $ids);
    }

    /**
     * Same direct-assignment semantics for DISTRICT (also city-grouper).
     */
    public function testZoneFilterDistrictExcludesCommuneAdherentAttachedOnlyToChild(): void
    {
        $districtZone = $this->getZoneByCode(LoadAudienceFilterTestData::ZONE_CODE_DISTRICT);

        $ids = $this->findIds(static function (AdherentMessageFilter $filter) use ($districtZone): void {
            $filter->setZone($districtZone);
        });

        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_COMMUNE_OF_DISTRICT), $ids);
    }

    public function testZonesManagedFilterIncludesAdherentInDepartment(): void
    {
        $departmentZone = $this->getZoneByCode('92');

        $ids = $this->findIds(static function (AdherentMessageFilter $filter) use ($departmentZone): void {
            $filter->getZones()->add($departmentZone);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_DEPARTMENT_92), $ids);
    }

    /**
     * Managed-zones perimeter must respect the city-grouper exception: a CANTON in the
     * managed list does not cascade to its children. Same semantics as the single-zone
     * filter (testZoneFilterCantonExcludesCommuneAdherentAttachedOnlyToChild).
     */
    public function testZonesManagedFilterCantonExcludesCommuneAdherentAttachedOnlyToChild(): void
    {
        $cantonZone = $this->getZoneByCode(LoadAudienceFilterTestData::ZONE_CODE_CANTON);

        $ids = $this->findIds(static function (AdherentMessageFilter $filter) use ($cantonZone): void {
            $filter->getZones()->add($cantonZone);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_CANTON_DIRECT), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_COMMUNE_OF_CANTON), $ids);
    }

    /**
     * Same managed-zones city-grouper semantics for DISTRICT.
     */
    public function testZonesManagedFilterDistrictExcludesCommuneAdherentAttachedOnlyToChild(): void
    {
        $districtZone = $this->getZoneByCode(LoadAudienceFilterTestData::ZONE_CODE_DISTRICT);

        $ids = $this->findIds(static function (AdherentMessageFilter $filter) use ($districtZone): void {
            $filter->getZones()->add($districtZone);
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_DISTRICT_DIRECT), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_ZONE_COMMUNE_OF_DISTRICT), $ids);
    }

    public function testPostalCodeFilterIncludesAdherentsByPrefix(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->postalCode = '75';
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_POSTAL_CODE_75001), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_POSTAL_CODE_69001), $ids);
    }

    public function testPostalCodeFilterMatchesExactCode(): void
    {
        $ids = $this->findIds(static function (AdherentMessageFilter $filter): void {
            $filter->postalCode = '69001';
        });

        $this->assertContains($this->idOf(LoadAudienceFilterTestData::EMAIL_POSTAL_CODE_69001), $ids);
        $this->assertNotContains($this->idOf(LoadAudienceFilterTestData::EMAIL_POSTAL_CODE_75001), $ids);
    }

    /**
     * @return list<int>
     */
    private function findIds(callable $configure): array
    {
        $author = $this->adherentRepository->findOneByEmail(LoadAudienceFilterTestData::EMAIL_MALE_YOUNG);
        $this->assertInstanceOf(Adherent::class, $author, 'LoadAudienceFilterTestData fixture must be loaded.');

        $message = AdherentMessage::createFromAdherent($author);
        $message->setInstanceScope(ScopeEnum::NATIONAL);

        $filter = new AdherentMessageFilter();
        $configure($filter);
        $message->setFilter($filter);

        return $this->adherentRepository->findAdherentIdsForMessage($message);
    }

    private function idOf(string $email): int
    {
        $adherent = $this->adherentRepository->findOneByEmail($email);
        $this->assertInstanceOf(Adherent::class, $adherent, \sprintf('Fixture adherent "%s" not found.', $email));

        return $adherent->getId();
    }

    private function getZoneByCode(string $code): Zone
    {
        $zone = $this->manager->getRepository(Zone::class)->findOneBy(['code' => $code]);
        $this->assertInstanceOf(Zone::class, $zone, \sprintf('Zone with code "%s" not found.', $code));

        return $zone;
    }
}

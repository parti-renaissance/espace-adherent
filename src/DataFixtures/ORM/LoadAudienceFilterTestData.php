<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Adherent\MandateTypeEnum;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\Committee;
use App\Entity\Geo\Zone;
use App\Entity\PostAddress;
use App\Membership\AdherentFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Orthogonal fixture dedicated to buildAudienceQueryPieces segmentation tests.
 * Each adherent carries a single discriminating attribute (email prefix "audience-filter-<key>@..."),
 * with all other attributes neutralized (no committee, zone, mandate, tag).
 *
 * Looked up by email in tests/Repository/AdherentRepositoryAudienceFilterTest.php.
 */
class LoadAudienceFilterTestData extends Fixture implements DependentFixtureInterface
{
    public const EMAIL_DOMAIN = 'audience-filter-test.local';
    public const EMAIL_PREFIX_GLOB = 'audience-filter-';

    public const EMAIL_MALE_YOUNG = 'audience-filter-male-young@audience-filter-test.local';
    public const EMAIL_FEMALE_OLD = 'audience-filter-female-old@audience-filter-test.local';
    public const EMAIL_NO_GENDER = 'audience-filter-no-gender@audience-filter-test.local';
    public const EMAIL_REGISTERED_2020 = 'audience-filter-registered-2020@audience-filter-test.local';
    public const EMAIL_REGISTERED_2024 = 'audience-filter-registered-2024@audience-filter-test.local';
    public const EMAIL_REGISTERED_EDGE_DAY_START = 'audience-filter-registered-edge-day-start@audience-filter-test.local';
    public const EMAIL_REGISTERED_EDGE_DAY_END = 'audience-filter-registered-edge-day-end@audience-filter-test.local';
    public const EMAIL_FIRST_MEMBERSHIP_2022 = 'audience-filter-first-membership-2022@audience-filter-test.local';
    public const EMAIL_LAST_MEMBERSHIP_2025 = 'audience-filter-last-membership-2025@audience-filter-test.local';
    public const EMAIL_NO_MEMBERSHIP = 'audience-filter-no-membership@audience-filter-test.local';
    public const EMAIL_TAGS_ADHERENT_RENAISSANCE = 'audience-filter-tags-adherent-renaissance@audience-filter-test.local';
    public const EMAIL_TAGS_ELECT_MAYOR = 'audience-filter-tags-elect-mayor@audience-filter-test.local';
    public const EMAIL_TAGS_STATIC_NEWSLETTER = 'audience-filter-tags-static-newsletter@audience-filter-test.local';
    public const EMAIL_DECLARED_MANDATE_MAIRE = 'audience-filter-declared-mandate-maire@audience-filter-test.local';
    public const EMAIL_ELECT_MANDATE_ACTIVE = 'audience-filter-elect-mandate-active@audience-filter-test.local';
    public const EMAIL_ELECT_MANDATE_PAST = 'audience-filter-elect-mandate-past@audience-filter-test.local';
    public const EMAIL_CERTIFIED = 'audience-filter-certified@audience-filter-test.local';
    public const EMAIL_DONATOR_CURRENT_YEAR = 'audience-filter-donator-current-year@audience-filter-test.local';
    public const EMAIL_FIRST_NAME_CHARLES = 'audience-filter-first-name-charles@audience-filter-test.local';
    public const EMAIL_LAST_NAME_SPECIAL = 'audience-filter-last-name-special@audience-filter-test.local';

    public const EMAIL_COMMITTEE_MEMBER = 'audience-filter-committee-member@audience-filter-test.local';
    public const EMAIL_ZONE_DEPARTMENT_92 = 'audience-filter-zone-department-92@audience-filter-test.local';
    public const EMAIL_ZONE_CANTON_DIRECT = 'audience-filter-zone-canton-direct@audience-filter-test.local';
    public const EMAIL_ZONE_COMMUNE_OF_CANTON = 'audience-filter-zone-commune-of-canton@audience-filter-test.local';
    public const EMAIL_ZONE_DISTRICT_DIRECT = 'audience-filter-zone-district-direct@audience-filter-test.local';
    public const EMAIL_ZONE_COMMUNE_OF_DISTRICT = 'audience-filter-zone-commune-of-district@audience-filter-test.local';
    public const EMAIL_POSTAL_CODE_75001 = 'audience-filter-postal-75001@audience-filter-test.local';
    public const EMAIL_POSTAL_CODE_69001 = 'audience-filter-postal-69001@audience-filter-test.local';

    public const FIRST_NAME_CHARLES = 'Charles-Audience-Filter';
    public const LAST_NAME_SPECIAL = 'Special-Audience-Filter';

    // Dedicated zone codes (prefix audience-filter to avoid colliding with global counts in other tests).
    public const ZONE_CODE_CANTON = 'AF-CANTON';
    public const ZONE_CODE_DISTRICT = 'AF-DISTRICT';
    public const ZONE_CODE_COMMUNE_OF_CANTON = 'AF-COMMUNE-CANTON';
    public const ZONE_CODE_COMMUNE_OF_DISTRICT = 'AF-COMMUNE-DISTRICT';

    public function __construct(private readonly AdherentFactory $adherentFactory)
    {
    }

    public function getDependencies(): array
    {
        return [
            LoadGeoZoneData::class,
            LoadCommitteeData::class,
            LoadSubscriptionTypeData::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        // Dedicated zones for audience-filter tests (avoid collisions with global zone counts in other tests).
        // - Canton + District (city-grouper types) for Phase 2 city-grouper bugfix tests.
        // - Communes attached to canton/district as parents — used to capture the current buggy behavior
        //   where a SQL filter on the parent canton/district incorrectly includes child commune adherents.
        $cantonZone = new Zone(Zone::CANTON, self::ZONE_CODE_CANTON, 'Audience Filter Canton');
        $manager->persist($cantonZone);

        $districtZone = new Zone(Zone::DISTRICT, self::ZONE_CODE_DISTRICT, 'Audience Filter District');
        $manager->persist($districtZone);

        $communeOfCantonZone = new Zone(Zone::CITY, self::ZONE_CODE_COMMUNE_OF_CANTON, 'Audience Filter Commune of Canton');
        $communeOfCantonZone->addParent($cantonZone);
        $manager->persist($communeOfCantonZone);

        $communeOfDistrictZone = new Zone(Zone::CITY, self::ZONE_CODE_COMMUNE_OF_DISTRICT, 'Audience Filter Commune of District');
        $communeOfDistrictZone->addParent($districtZone);
        $manager->persist($communeOfDistrictZone);

        // Adherents for the `gender` filter
        $maleYoung = $this->createAdherent($manager, [
            'email' => self::EMAIL_MALE_YOUNG,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '2000-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);

        $femaleOld = $this->createAdherent($manager, [
            'email' => self::EMAIL_FEMALE_OLD,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'female',
            'birthdate' => '1960-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);

        $this->createAdherent($manager, [
            'email' => self::EMAIL_NO_GENDER,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => null,
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);

        // Adherents for the `registered` filter
        $this->createAdherent($manager, [
            'email' => self::EMAIL_REGISTERED_2020,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2020-06-15 12:00:00',
        ]);

        $this->createAdherent($manager, [
            'email' => self::EMAIL_REGISTERED_2024,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-03-10 08:00:00',
        ]);

        $this->createAdherent($manager, [
            'email' => self::EMAIL_REGISTERED_EDGE_DAY_START,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2023-01-01 00:00:00',
        ]);

        $this->createAdherent($manager, [
            'email' => self::EMAIL_REGISTERED_EDGE_DAY_END,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2023-01-01 23:59:59',
        ]);

        // Adherents for the membership donation filters
        $firstMembership2022 = $this->createAdherent($manager, [
            'email' => self::EMAIL_FIRST_MEMBERSHIP_2022,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $firstMembership2022->setFirstMembershipDonation(new \DateTime('2022-04-01'));
        // First donation = last donation for this adherent (never donated again).
        $firstMembership2022->setLastMembershipDonation(new \DateTime('2022-04-01'));

        $lastMembership2025 = $this->createAdherent($manager, [
            'email' => self::EMAIL_LAST_MEMBERSHIP_2025,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $lastMembership2025->setFirstMembershipDonation(new \DateTime('2020-01-01'));
        $lastMembership2025->setLastMembershipDonation(new \DateTime('2025-02-15'));

        $this->createAdherent($manager, [
            'email' => self::EMAIL_NO_MEMBERSHIP,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);

        // Adherents for the tag filters
        $tagAdherent = $this->createAdherent($manager, [
            'email' => self::EMAIL_TAGS_ADHERENT_RENAISSANCE,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        // adherentTags uses LIKE prefix → use a stable prefix such as `adherent:renaissance:`
        $tagAdherent->tags = ['adherent:renaissance:supporter'];

        $tagElect = $this->createAdherent($manager, [
            'email' => self::EMAIL_TAGS_ELECT_MAYOR,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        // electTags / staticTags use LIKE contains
        $tagElect->tags = ['elect:mayor:active'];

        $tagStatic = $this->createAdherent($manager, [
            'email' => self::EMAIL_TAGS_STATIC_NEWSLETTER,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $tagStatic->tags = ['static:newsletter:abonne'];

        // Adherent for declaredMandate
        $declaredMandate = $this->createAdherent($manager, [
            'email' => self::EMAIL_DECLARED_MANDATE_MAIRE,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $declaredMandate->setMandates(['maire', 'president']);

        // Adherents for electMandate (active vs ended)
        $electMandateActive = $this->createAdherent($manager, [
            'email' => self::EMAIL_ELECT_MANDATE_ACTIVE,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $manager->persist(ElectedRepresentativeAdherentMandate::create(
            null,
            $electMandateActive,
            MandateTypeEnum::CONSEILLER_MUNICIPAL,
            new \DateTime('2020-01-01'),
            null, // active mandate
        ));

        $electMandatePast = $this->createAdherent($manager, [
            'email' => self::EMAIL_ELECT_MANDATE_PAST,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $manager->persist(ElectedRepresentativeAdherentMandate::create(
            null,
            $electMandatePast,
            MandateTypeEnum::CONSEILLER_MUNICIPAL,
            new \DateTime('2014-01-01'),
            new \DateTime('2020-01-01'), // ended mandate
        ));

        // Certified adherent
        $certified = $this->createAdherent($manager, [
            'email' => self::EMAIL_CERTIFIED,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $certified->certify();

        // Current-year donator (DONATOR_N)
        $currentYear = (int) date('Y');
        $donator = $this->createAdherent($manager, [
            'email' => self::EMAIL_DONATOR_CURRENT_YEAR,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $donator->setFirstMembershipDonation(new \DateTime($currentYear.'-01-15'));
        $donator->setLastMembershipDonation(new \DateTime($currentYear.'-06-15'));

        // Adherents with distinct firstName / lastName (exact-match test)
        $this->createAdherent($manager, [
            'email' => self::EMAIL_FIRST_NAME_CHARLES,
            'first_name' => self::FIRST_NAME_CHARLES,
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);

        $this->createAdherent($manager, [
            'email' => self::EMAIL_LAST_NAME_SPECIAL,
            'first_name' => 'Audience',
            'last_name' => self::LAST_NAME_SPECIAL,
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);

        // Adherent member of an existing committee (committee-v2-1 from LoadCommitteeData).
        // Used by both the `committee` filter and the `isCommitteeMember` filter tests.
        $committeeMember = $this->createAdherent($manager, [
            'email' => self::EMAIL_COMMITTEE_MEMBER,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $committee = $this->getReference('committee-v2-1', Committee::class);
        $manager->persist($committeeMember->followCommittee(
            $committee,
            new \DateTime('-2 months'),
            CommitteeMembershipTriggerEnum::MANUAL,
        ));

        // Adherent attached to an existing department zone (zone_department_92).
        // Used as a non-city-grouper baseline (parent lookup is expected to apply).
        $departmentZoneAdherent = $this->createAdherent($manager, [
            'email' => self::EMAIL_ZONE_DEPARTMENT_92,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $departmentZoneAdherent->addZone(LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'));

        // Adherent attached directly to the dedicated canton zone (city-grouper).
        $cantonDirectAdherent = $this->createAdherent($manager, [
            'email' => self::EMAIL_ZONE_CANTON_DIRECT,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $cantonDirectAdherent->addZone($cantonZone);

        // Adherent attached only to a commune that has the canton as parent.
        // Captures the current buggy behavior: SQL email path incorrectly includes this adherent
        // when filtering on the parent canton (Phase 2 will fix this).
        $communeOfCantonAdherent = $this->createAdherent($manager, [
            'email' => self::EMAIL_ZONE_COMMUNE_OF_CANTON,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $communeOfCantonAdherent->addZone($communeOfCantonZone);

        // Adherent attached directly to the dedicated district zone (city-grouper).
        $districtDirectAdherent = $this->createAdherent($manager, [
            'email' => self::EMAIL_ZONE_DISTRICT_DIRECT,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $districtDirectAdherent->addZone($districtZone);

        // Adherent attached only to a commune that has the district as parent.
        // Same role as the commune-of-canton adherent for the district city-grouper case.
        $communeOfDistrictAdherent = $this->createAdherent($manager, [
            'email' => self::EMAIL_ZONE_COMMUNE_OF_DISTRICT,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $communeOfDistrictAdherent->addZone($communeOfDistrictZone);

        // Adherents with a postal code, used to exercise the `postalCode` LIKE prefix filter.
        $this->createAdherent($manager, [
            'email' => self::EMAIL_POSTAL_CODE_75001,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
            'address' => PostAddress::createFrenchAddress('1 rue Example', '75001-75056', 'Paris'),
        ]);

        $this->createAdherent($manager, [
            'email' => self::EMAIL_POSTAL_CODE_69001,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
            'address' => PostAddress::createFrenchAddress('2 rue Example', '69001-69123', 'Lyon'),
        ]);

        $manager->flush();
    }

    private function createAdherent(ObjectManager $manager, array $data): Adherent
    {
        $adherent = $this->adherentFactory->createFromArray(array_merge([
            'password' => 'audience-filter-test-pwd',
            'address' => null,
        ], $data));

        $adherent->setStatus(Adherent::ENABLED);

        $manager->persist($adherent);

        return $adherent;
    }
}

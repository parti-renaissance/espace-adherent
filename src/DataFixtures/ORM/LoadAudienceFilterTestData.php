<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Adherent\MandateTypeEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Membership\AdherentFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Orthogonal fixture dedicated to buildAudienceQueryPieces segmentation tests.
 * Each adherent carries a single discriminating attribute (email prefix "audience-filter-<key>@..."),
 * with all other attributes neutralized (no committee, zone, mandate, tag).
 *
 * Looked up by email in tests/Repository/AdherentRepositoryAudienceFilterTest.php.
 */
class LoadAudienceFilterTestData extends Fixture
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

    public const FIRST_NAME_CHARLES = 'Charles-Audience-Filter';
    public const LAST_NAME_SPECIAL = 'Special-Audience-Filter';

    public function __construct(private readonly AdherentFactory $adherentFactory)
    {
    }

    public function load(ObjectManager $manager): void
    {
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

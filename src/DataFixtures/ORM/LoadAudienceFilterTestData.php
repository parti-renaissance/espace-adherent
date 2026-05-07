<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Adherent\MandateTypeEnum;
use App\Committee\CommitteeMembershipTriggerEnum;
use App\Entity\Adherent;
use App\Entity\AdherentMandate\ElectedRepresentativeAdherentMandate;
use App\Entity\Committee;
use App\Entity\SubscriptionType;
use App\Membership\AdherentFactory;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

/**
 * Fixture orthogonale dédiée aux tests de segmentation buildAudienceQueryPieces.
 * Chaque adhérent porte un seul attribut discriminant (préfixe email "audience-filter-<key>@example.test"),
 * tous les autres attributs sont neutralisés (pas de committee, zone, mandat, tag).
 *
 * Référencés par email dans tests/Repository/AdherentRepositoryAudienceFilterTest.php
 */
class LoadAudienceFilterTestData extends Fixture implements DependentFixtureInterface
{
    public const EMAIL_DOMAIN = 'audience-filter-test.local';
    public const EMAIL_PREFIX_GLOB = 'audience-filter-';

    public const COMMITTEE_UUID = '0e3a9cf3-99e7-4e3b-9b97-1e2f48a9d111';
    public const ZONE_REFERENCE = 'zone_city_75056';

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
    public const EMAIL_COMMITTEE_AUTHOR = 'audience-filter-committee-author@audience-filter-test.local';
    public const EMAIL_ZONE_PARIS = 'audience-filter-zone-paris@audience-filter-test.local';
    public const EMAIL_SUBSCRIBED_DEPUTY = 'audience-filter-subscribed-deputy@audience-filter-test.local';

    public const FIRST_NAME_CHARLES = 'Charles-Audience-Filter';
    public const LAST_NAME_SPECIAL = 'Special-Audience-Filter';

    public function __construct(private readonly AdherentFactory $adherentFactory)
    {
    }

    public function load(ObjectManager $manager): void
    {
        // Adhérents pour le filtre `gender`
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

        // Adhérents pour le filtre `registered`
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

        // Adhérents pour les filtres de cotisation
        $firstMembership2022 = $this->createAdherent($manager, [
            'email' => self::EMAIL_FIRST_MEMBERSHIP_2022,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $firstMembership2022->setFirstMembershipDonation(new \DateTime('2022-04-01'));
        // Premier don = dernier don pour cet adhérent (jamais redonné).
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

        // Adhérents pour les filtres de tags
        $tagAdherent = $this->createAdherent($manager, [
            'email' => self::EMAIL_TAGS_ADHERENT_RENAISSANCE,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        // adherentTags utilise LIKE prefix → préfixe stable comme `adherent:renaissance:`
        $tagAdherent->tags = ['adherent:renaissance:supporter'];

        $tagElect = $this->createAdherent($manager, [
            'email' => self::EMAIL_TAGS_ELECT_MAYOR,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        // electTags / staticTags utilisent LIKE contains
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

        // Adhérent pour declaredMandate
        $declaredMandate = $this->createAdherent($manager, [
            'email' => self::EMAIL_DECLARED_MANDATE_MAIRE,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $declaredMandate->setMandates(['maire', 'president']);

        // Adhérents pour electMandate (actif vs historique)
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
            null, // mandat actif
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
            new \DateTime('2020-01-01'), // mandat terminé
        ));

        // Adhérent certifié
        $certified = $this->createAdherent($manager, [
            'email' => self::EMAIL_CERTIFIED,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $certified->certify();

        // Adhérent donateur de l'année courante (DONATOR_N)
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

        // Adhérent avec firstName / lastName distincts (test match exact)
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

        // Committee dédié + adhérent membre (filtres `committee` et `isCommitteeMember`)
        $committeeAuthor = $this->createAdherent($manager, [
            'email' => self::EMAIL_COMMITTEE_AUTHOR,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);

        $committee = Committee::createSimple(
            Uuid::fromString(self::COMMITTEE_UUID),
            $committeeAuthor->getUuidAsString(),
            'Audience Filter Test Committee',
            'Comité dédié aux tests fonctionnels de segmentation.',
        );
        $committee->approved();
        $committee->addZone(LoadGeoZoneData::getZoneReference($manager, self::ZONE_REFERENCE));
        $manager->persist($committee);

        $committeeMember = $this->createAdherent($manager, [
            'email' => self::EMAIL_COMMITTEE_MEMBER,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $manager->persist($committeeMember->followCommittee(
            $committee,
            new \DateTime('-1 year'),
            CommitteeMembershipTriggerEnum::COMMITTEE_EDITION,
        ));

        // Adhérent rattaché à une zone (filtres `zone` et `zones`)
        $zoneAdherent = $this->createAdherent($manager, [
            'email' => self::EMAIL_ZONE_PARIS,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $zoneAdherent->addZone(LoadGeoZoneData::getZoneReference($manager, self::ZONE_REFERENCE));

        // Adhérent souscrit à un subscription type (filtre `scope`)
        $subscribedDeputy = $this->createAdherent($manager, [
            'email' => self::EMAIL_SUBSCRIBED_DEPUTY,
            'first_name' => 'Audience',
            'last_name' => 'Filter',
            'gender' => 'male',
            'birthdate' => '1985-01-01',
            'registered_at' => '2024-01-01 12:00:00',
        ]);
        $subscribedDeputy->addSubscriptionType(
            $this->getReference('st-'.SubscriptionTypeEnum::DEPUTY_EMAIL, SubscriptionType::class),
        );

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LoadGeoZoneData::class,
            LoadSubscriptionTypeData::class,
        ];
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

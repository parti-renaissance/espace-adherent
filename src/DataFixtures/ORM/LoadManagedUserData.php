<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Adherent\MandateTypeEnum;
use App\Adherent\Tag\TagEnum;
use App\Entity\Adherent;
use App\Entity\Agora;
use App\Entity\Committee;
use App\Entity\Projection\ManagedUser;
use App\Mailchimp\Contact\ContactStatusEnum;
use App\Membership\MembershipSourceEnum;
use App\Subscription\SubscriptionTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadManagedUserData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $committee1 = $this->getReference('committee-1', Committee::class);
        $committee3 = $this->getReference('committee-3', Committee::class);
        $committee4 = $this->getReference('committee-4', Committee::class);
        $committee5 = $this->getReference('committee-5', Committee::class);
        $committee10 = $this->getReference('committee-10', Committee::class);
        $committee11 = $this->getReference('committee-v2-2', Committee::class);

        $agora1 = $this->getReference('agora-1', Agora::class);

        // ManagedUser 1 - adherent-1
        $adherent = $this->getReference('adherent-1', Adherent::class);
        $managedUser1 = $this->createManagedUser($adherent, [
            'subscription_types' => [SubscriptionTypeEnum::MILITANT_ACTION_SMS],
            'subscribed_tags' => 'ch',
            'zones' => [LoadGeoZoneData::getZone($manager, 'zone_country_CH')],
        ]);
        $manager->persist($managedUser1);

        // ManagedUser 2 - adherent-13
        $adherent = $this->getReference('adherent-13', Adherent::class);
        $managedUser2 = $this->createManagedUser($adherent, [
            'committees' => $committee10->getName(),
            'committee_uuids' => [$committee10->getUuid()->toString()],
            'is_committee_member' => true,
            'subscription_types' => [SubscriptionTypeEnum::REFERENT_EMAIL, SubscriptionTypeEnum::MILITANT_ACTION_SMS],
            'subscribed_tags' => 'ch',
            'zones' => [LoadGeoZoneData::getZone($manager, 'zone_country_CH')],
        ]);
        $manager->persist($managedUser2);

        // ManagedUser 3 - adherent-5 (Gisele Berthoux)
        $adherent = $this->getReference('adherent-5', Adherent::class);
        $managedUser3 = $this->createManagedUser($adherent, [
            'committees' => $committee1->getName(),
            'committee_uuids' => [$committee1->getUuid()->toString()],
            'is_committee_member' => true,
            'is_committee_host' => true,
            'subscription_types' => [SubscriptionTypeEnum::REFERENT_EMAIL, SubscriptionTypeEnum::MILITANT_ACTION_SMS],
            'subscribed_tags' => '92,59',
            'zones' => [
                LoadGeoZoneData::getZone($manager, 'zone_department_92'),
                LoadGeoZoneData::getZone($manager, 'zone_city_92024'),
            ],
            'committee' => $committee11->getName(),
            'committee_uuid' => $committee11->getUuid(),
            'agora' => $agora1->getName(),
            'agora_uuid' => $agora1->getUuid(),
            'mandates' => [MandateTypeEnum::CONSEILLER_MUNICIPAL],
            'civility' => 'Madame',
            'instances' => [
                ['type' => 'assembly', 'code' => '92', 'name' => 'Hauts-de-Seine (92)'],
                ['type' => 'committee', 'name' => 'En Marche Paris 8', 'uuid' => '515a56c0-bde8-56ef-b90c-4745b1c93818'],
            ],
            'subscriptions' => [
                'mobile' => ['available' => true, 'subscribed' => false],
                'web' => ['available' => true, 'subscribed' => true],
                'sms' => ['available' => true, 'subscribed' => true],
                'email' => ['available' => true, 'subscribed' => true],
            ],
            // Tags from adherent-5 (should match LoadAdherentData)
            'override_tags' => [
                TagEnum::getAdherentYearTag(null, TagEnum::ADHERENT_YEAR_RECOTISATION_TAG_PATTERN),
                TagEnum::ELU_COTISATION_OK_EXEMPTE,
                TagEnum::getNationalEventTag('event-national-1', false),
            ],
        ]);
        $manager->persist($managedUser3);

        // ManagedUser 4 - adherent-7 (Francis Brioul)
        $adherent = $this->getReference('adherent-7', Adherent::class);
        $managedUser4 = $this->createManagedUser($adherent, [
            'override_tags' => [
                TagEnum::getAdherentYearTag(2024),
                TagEnum::ELU_COTISATION_OK_SOUMIS,
                TagEnum::getNationalEventTag('congres-2024', true),
            ],
            'committee_postal_code' => '91',
            'committees' => implode('|', [$committee3->getName(), $committee4->getName(), $committee5->getName()]),
            'committee_uuids' => [$committee3->getUuid()->toString(), $committee4->getUuid()->toString(), $committee5->getUuid()->toString()],
            'vote_committee_id' => $committee3->getId(),
            'is_committee_member' => true,
            'is_committee_supervisor' => true,
            'subscribed_tags' => '77,59',
            'zones' => [
                LoadGeoZoneData::getZone($manager, 'zone_district_77-1'),
                LoadGeoZoneData::getZone($manager, 'zone_city_77288'),
            ],
            'supervisor_tags' => ['77'],
            'civility' => 'Monsieur',
            'sessions' => [
                'mobile' => [
                    [
                        'device' => 'iPhone 14',
                        'active_since' => '2024-01-15T10:30:00+01:00',
                        'last_activity_at' => '2024-03-01T14:22:00+01:00',
                        'subscribed' => true,
                        'status' => 'active',
                    ],
                ],
                'web' => null,
            ],
            'instances' => [
                ['type' => 'assembly', 'code' => '77', 'name' => 'Seine-et-Marne (77)'],
                ['type' => 'circonscription', 'code' => '77-1', 'name' => '1ère circonscription • Seine-et-Marne (77-1)'],
            ],
            'subscriptions' => [
                'mobile' => ['available' => true, 'subscribed' => true],
                'web' => ['available' => true, 'subscribed' => false],
                'sms' => ['available' => false, 'subscribed' => false],
                'email' => ['available' => true, 'subscribed' => true],
            ],
        ]);
        $manager->persist($managedUser4);

        // ManagedUser 5 - adherent-3
        $adherent = $this->getReference('adherent-3', Adherent::class);
        $managedUser5 = $this->createManagedUser($adherent, [
            'is_committee_member' => true,
            'is_committee_host' => true,
            'is_committee_supervisor' => true,
            'subscription_types' => array_merge(SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES, [SubscriptionTypeEnum::MILITANT_ACTION_SMS]),
            'subscribed_tags' => '75,75008,CIRCO_75001',
            'zones' => [
                LoadGeoZoneData::getZone($manager, 'zone_borough_75108'),
                LoadGeoZoneData::getZone($manager, 'zone_district_75-1'),
            ],
            'cotisation_dates' => [new \DateTime('2022-02-01 12:00:00'), new \DateTime('2023-03-01 12:00:00')],
        ]);
        $manager->persist($managedUser5);

        // ManagedUser 6 - deputy-75-1
        $adherent = $this->getReference('deputy-75-1', Adherent::class);
        $managedUser6 = $this->createManagedUser($adherent, [
            'subscription_types' => array_merge(SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES, [SubscriptionTypeEnum::MILITANT_ACTION_SMS]),
            'subscribed_tags' => '75,75008,CIRCO_75001',
            'zones' => [
                LoadGeoZoneData::getZone($manager, 'zone_borough_75108'),
                LoadGeoZoneData::getZone($manager, 'zone_district_75-1'),
            ],
            'cotisation_dates' => [new \DateTime('2022-01-01 12:00:00'), new \DateTime('2023-01-01 12:00:00')],
        ]);
        $manager->persist($managedUser6);

        // ManagedUser 7 - correspondent-1
        $adherent = $this->getReference('correspondent-1', Adherent::class);
        $managedUser7 = $this->createManagedUser($adherent, [
            'source' => MembershipSourceEnum::JEMENGAGE,
            'zones' => [
                LoadGeoZoneData::getZone($manager, 'zone_department_92'),
                LoadGeoZoneData::getZone($manager, 'zone_department_59'),
            ],
            'civility' => 'Monsieur',
            'instances' => [
                ['type' => 'assembly', 'code' => '92', 'name' => 'Hauts-de-Seine (92)'],
            ],
            'subscriptions' => [
                'mobile' => ['available' => true, 'subscribed' => true],
                'web' => ['available' => true, 'subscribed' => true],
                'sms' => ['available' => false, 'subscribed' => false],
                'email' => ['available' => true, 'subscribed' => true],
            ],
        ]);
        $manager->persist($managedUser7);

        $manager->flush();
    }

    private function createManagedUser(Adherent $adherent, array $options = []): ManagedUser
    {
        $birthdate = $adherent->getBirthdate();
        $age = $birthdate ? $this->calculateAge($birthdate) : null;

        $managedUser = new ManagedUser(
            status: ManagedUser::STATUS_READY,
            source: $options['source'] ?? null,
            originalId: $adherent->getId(),
            email: $adherent->getEmailAddress(),
            address: $adherent->getAddress() ?? '',
            postalCode: $adherent->getPostalCode() ?? '',
            committeePostalCode: $options['committee_postal_code'] ?? null,
            city: $adherent->getCityName(),
            country: $adherent->getCountry(),
            firstName: $adherent->getFirstName(),
            lastName: $adherent->getLastName(),
            birthdate: $birthdate,
            age: $age,
            phone: $adherent->getPhone(),
            nationality: $adherent->getNationality(),
            committees: $options['committees'] ?? null,
            committeeUuids: $options['committee_uuids'] ?? null,
            tags: $adherent->tags,
            isCommitteeMember: (int) ($options['is_committee_member'] ?? false),
            isCommitteeHost: (int) ($options['is_committee_host'] ?? false),
            isCommitteeSupervisor: (int) ($options['is_committee_supervisor'] ?? false),
            subscriptionTypes: $options['subscription_types'] ?? [],
            zones: $options['zones'] ?? [],
            subscribedTags: $options['subscribed_tags'] ?? null,
            createdAt: $adherent->getRegisteredAt(),
            gender: $adherent->getGender(),
            supervisorTags: $options['supervisor_tags'] ?? [],
            uuid: $adherent->getUuid(),
            voteCommitteeId: $options['vote_committee_id'] ?? null,
            certifiedAt: $adherent->getCertifiedAt(),
            lastMembershipDonation: $options['cotisation_dates'][1] ?? null,
            firstMembershipDonation: $options['cotisation_dates'][0] ?? null,
            committee: $options['committee'] ?? null,
            committeeUuid: $options['committee_uuid'] ?? null,
            agora: $options['agora'] ?? null,
            agoraUuid: $options['agora_uuid'] ?? null,
            interests: $adherent->getInterests(),
            mandates: $options['mandates'] ?? [],
            declaredMandates: $adherent->getMandates() ?? [],
            cotisationDates: $this->formatCotisationDates($options['cotisation_dates'] ?? []),
            imageName: $adherent->getImageName(),
        );

        $managedUser->mailchimpStatus = ContactStatusEnum::SUBSCRIBED;

        // Split adherent tags into 3 categories
        // Note: We get tags directly from adherent but also allow override via options
        $tags = $options['override_tags'] ?? $adherent->tags;
        [$adherentTags, $electTags, $staticTags] = $this->splitTags($tags);
        $managedUser->adherentTags = $adherentTags;
        $managedUser->electTags = $electTags;
        $managedUser->staticTags = $staticTags;

        // Set additional VOX fields
        if (isset($options['civility'])) {
            $managedUser->civility = $options['civility'];
        }
        if (isset($options['sessions'])) {
            $managedUser->sessions = $options['sessions'];
        }
        if (isset($options['instances'])) {
            $managedUser->instances = $options['instances'];
        }
        if (isset($options['subscriptions'])) {
            $managedUser->subscriptions = $options['subscriptions'];
        }

        $managedUser->setRoles($this->getRoles($adherent));

        return $managedUser;
    }

    private function splitTags(array $tags): array
    {
        if (empty($tags)) {
            return [null, null, null];
        }

        $adherentTags = [];
        $electTags = [];
        $staticTags = [];

        foreach ($tags as $tag) {
            $tagData = [
                'code' => $tag,
                'label' => $this->generateTagLabel($tag),
            ];

            if (str_starts_with($tag, TagEnum::ADHERENT.':') || str_starts_with($tag, TagEnum::SYMPATHISANT.':')) {
                $adherentTags[] = $tagData;
            } elseif (str_starts_with($tag, TagEnum::ELU.':')) {
                $electTags[] = $tagData;
            } else {
                $staticTags[] = $tagData;
            }
        }

        return [
            $adherentTags ?: null,
            $electTags ?: null,
            $staticTags ?: null,
        ];
    }

    private function generateTagLabel(string $tag): string
    {
        $parts = explode(':', $tag);
        $lastPart = end($parts);

        return ucfirst(str_replace(['_', '-'], ' ', $lastPart));
    }

    private function calculateAge(\DateTimeInterface $birthdate): int
    {
        $now = new \DateTime();
        $age = (int) $now->format('Y') - (int) $birthdate->format('Y');

        if ((int) $now->format('md') < (int) $birthdate->format('md')) {
            --$age;
        }

        return $age;
    }

    private function formatCotisationDates(array $dates): array
    {
        return array_map(
            fn ($date) => $date instanceof \DateTimeInterface ? $date->format('Y-m-d H:i:s') : $date,
            $dates
        );
    }

    private function getRoles(Adherent $adherent): array
    {
        $roles = [];

        foreach ($adherent->getZoneBasedRoles() as $role) {
            $zones = $role->getZones()->toArray();
            $zoneNames = array_map(fn ($zone) => $zone->getName(), $zones);
            $zoneCodes = array_map(fn ($zone) => $zone->getCode(), $zones);
            sort($zoneNames);
            sort($zoneCodes);

            $roles[] = [
                'code' => $role->getType(),
                'is_delegated' => false,
                'function' => null,
                'zones' => $zoneNames ? implode('. ', $zoneNames) : null,
                'zone_codes' => $zoneCodes ? implode(', ', $zoneCodes) : null,
            ];
        }

        foreach ($adherent->getReceivedDelegatedAccesses() as $delegatedAccess) {
            $roles[] = [
                'code' => $delegatedAccess->getType(),
                'is_delegated' => true,
                'function' => $delegatedAccess->getRole(),
                'zones' => null,
                'zone_codes' => null,
            ];
        }

        return $roles;
    }

    public function getDependencies(): array
    {
        return [
            LoadCommitteeV1Data::class,
            LoadDelegatedAccessData::class,
            LoadGeoZoneData::class,
        ];
    }
}

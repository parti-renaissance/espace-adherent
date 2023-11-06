<?php

namespace App\DataFixtures\ORM;

use App\Address\AddressInterface;
use App\Adherent\MandateTypeEnum;
use App\Entity\Adherent;
use App\Entity\Projection\ManagedUser;
use App\Entity\Projection\ManagedUserFactory;
use App\Membership\MembershipSourceEnum;
use App\Subscription\SubscriptionTypeEnum;
use App\Utils\PhoneNumberUtils;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadManagedUserData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $managedUserFactory = $this->getManagedUserFactory();

        $committee1 = $this->getReference('committee-1');
        $committee3 = $this->getReference('committee-3');
        $committee4 = $this->getReference('committee-4');
        $committee5 = $this->getReference('committee-5');
        $committee10 = $this->getReference('committee-10');
        $committee11 = $this->getReference('committee-v2-2');

        /** @var Adherent $adherent */
        $adherent = $this->getReference('adherent-1');
        $managedUser1 = $managedUserFactory->createFromArray([
            'status' => ManagedUser::STATUS_READY,
            'source' => null,
            'original_id' => $adherent->getId(),
            'uuid' => $adherent->getUuid(),
            'email' => $adherent->getEmailAddress(),
            'postal_code' => $adherent->getPostalCode(),
            'address' => $adherent->getAddress(),
            'city' => $adherent->getCityName(),
            'country' => $adherent->getCountry(),
            'first_name' => $adherent->getFirstName(),
            'last_name' => $adherent->getLastName(),
            'birthday' => $adherent->getBirthdate(),
            'phone' => PhoneNumberUtils::create('+33666666666'),
            'nationality' => AddressInterface::FRANCE,
            'is_committee_member' => 0,
            'is_committee_host' => 0,
            'is_committee_provisional_supervisor' => 0,
            'is_committee_supervisor' => 0,
            'subscription_types' => [SubscriptionTypeEnum::MILITANT_ACTION_SMS],
            'subscribedTags' => 'ch',
            'zones' => [
                LoadGeoZoneData::getZone($manager, 'zone_country_CH'), // Suisse
            ],
            'created_at' => '2017-06-01 09:22:45',
            'gender' => 'male',
            'certified_at' => '2018-06-01 10:20:45',
            'interests' => ['europe', 'numerique', 'sante'],
            'declared_mandates' => [MandateTypeEnum::CONSEILLER_MUNICIPAL, MandateTypeEnum::MAIRE],
            'tags' => $adherent->tags,
        ]);

        /** @var Adherent $adherent */
        $adherent = $this->getReference('adherent-13');
        $managedUser2 = $managedUserFactory->createFromArray([
            'status' => ManagedUser::STATUS_READY,
            'source' => null,
            'original_id' => $adherent->getId(),
            'uuid' => $adherent->getUuid(),
            'email' => $adherent->getEmailAddress(),
            'postal_code' => $adherent->getPostalCode(),
            'address' => $adherent->getAddress(),
            'city' => $adherent->getCityName(),
            'country' => $adherent->getCountry(),
            'first_name' => $adherent->getFirstName(),
            'last_name' => $adherent->getLastName(),
            'birthday' => $adherent->getBirthdate(),
            'committees' => $committee10->getName(),
            'committee_uuids' => [$committee10->getUuid()->toString()],
            'phone' => PhoneNumberUtils::create('+33666666666'),
            'nationality' => AddressInterface::FRANCE,
            'is_committee_member' => 1,
            'is_committee_host' => 0,
            'is_committee_provisional_supervisor' => 0,
            'is_committee_supervisor' => 0,
            'subscription_types' => [SubscriptionTypeEnum::REFERENT_EMAIL, SubscriptionTypeEnum::MILITANT_ACTION_SMS],
            'subscribedTags' => 'ch',
            'zones' => [
                LoadGeoZoneData::getZone($manager, 'zone_country_CH'), // Suisse
            ],
            'created_at' => '2017-06-02 15:34:12',
            'gender' => 'male',
            'interests' => ['numerique'],
            'declared_mandates' => [MandateTypeEnum::DEPUTE_EUROPEEN],
            'tags' => $adherent->tags,
        ]);

        /** @var Adherent $adherent */
        $adherent = $this->getReference('adherent-5');
        $managedUser3 = $managedUserFactory->createFromArray([
            'status' => ManagedUser::STATUS_READY,
            'source' => null,
            'original_id' => $adherent->getId(),
            'uuid' => $adherent->getUuid(),
            'email' => $adherent->getEmailAddress(),
            'postal_code' => $adherent->getPostalCode(),
            'address' => $adherent->getAddress(),
            'city' => $adherent->getCityName(),
            'country' => $adherent->getCountry(),
            'first_name' => $adherent->getFirstName(),
            'last_name' => $adherent->getLastName(),
            'birthday' => $adherent->getBirthdate(),
            'committees' => $committee1->getName(),
            'committee_uuids' => [$committee1->getUuid()->toString()],
            'phone' => PhoneNumberUtils::create('+33666666666'),
            'nationality' => AddressInterface::FRANCE,
            'is_committee_member' => 1,
            'is_committee_host' => 1,
            'is_committee_provisional_supervisor' => 1,
            'is_committee_supervisor' => 0,
            'subscription_types' => [SubscriptionTypeEnum::REFERENT_EMAIL, SubscriptionTypeEnum::MILITANT_ACTION_SMS],
            'subscribedTags' => '92,59',
            'zones' => [
                LoadGeoZoneData::getZone($manager, 'zone_department_92'), // Hauts-de-Seine
                LoadGeoZoneData::getZone($manager, 'zone_city_92024'), // Clichy
            ],
            'created_at' => '2017-06-02 15:34:12',
            'gender' => 'female',
            'certified_at' => '2018-06-02 10:20:45',
            'committee' => $committee11->getName(),
            'committee_uuid' => $committee11->getUuid(),
            'mandates' => [MandateTypeEnum::CONSEILLER_MUNICIPAL.'|Métropole du Grand Paris'],
            'declared_mandates' => [MandateTypeEnum::CONSEILLER_MUNICIPAL],
            'tags' => $adherent->tags,
        ]);

        /** @var Adherent $adherent */
        $adherent = $this->getReference('adherent-7');
        $managedUser4 = $managedUserFactory->createFromArray([
            'status' => ManagedUser::STATUS_READY,
            'source' => null,
            'original_id' => $adherent->getId(),
            'uuid' => $adherent->getUuid(),
            'email' => $adherent->getEmailAddress(),
            'postal_code' => $adherent->getPostalCode(),
            'committee_postal_code' => '91',
            'address' => $adherent->getAddress(),
            'city' => $adherent->getCityName(),
            'country' => $adherent->getCountry(),
            'first_name' => $adherent->getFirstName(),
            'last_name' => $adherent->getLastName(),
            'birthday' => $adherent->getBirthdate(),
            'committees' => implode('|', [$committee3->getName(), $committee4->getName(), $committee5->getName()]),
            'committee_uuids' => [$committee3->getUuid()->toString(), $committee4->getUuid()->toString(), $committee5->getUuid()->toString()],
            'vote_committee_id' => $committee3->getId(),
            'is_committee_member' => 1,
            'is_committee_host' => 0,
            'is_committee_provisional_supervisor' => 0,
            'is_committee_supervisor' => 1,
            'subscription_types' => null,
            'subscribedTags' => '77,59',
            'zones' => [
                LoadGeoZoneData::getZone($manager, 'zone_district_77-1'), // Seine-et-Marne (1)
                LoadGeoZoneData::getZone($manager, 'zone_city_77288'), // Melun
            ],
            'created_at' => '2017-08-12 16:12:13',
            'gender' => 'male',
            'supervisor_tags' => ['77'],
            'tags' => $adherent->tags,
        ]);

        /** @var Adherent $adherent */
        $adherent = $this->getReference('adherent-3');
        $managedUser5 = $managedUserFactory->createFromArray([
            'status' => ManagedUser::STATUS_READY,
            'source' => null,
            'original_id' => $adherent->getId(),
            'uuid' => $adherent->getUuid(),
            'email' => $adherent->getEmailAddress(),
            'postal_code' => $adherent->getPostalCode(),
            'address' => $adherent->getAddress(),
            'city' => $adherent->getCityName(),
            'country' => $adherent->getCountry(),
            'first_name' => $adherent->getFirstName(),
            'last_name' => $adherent->getLastName(),
            'birthday' => $adherent->getBirthdate(),
            'phone' => PhoneNumberUtils::create('+33187264236'),
            'nationality' => AddressInterface::FRANCE,
            'is_committee_member' => 1,
            'is_committee_host' => 1,
            'is_committee_provisional_supervisor' => 0,
            'is_committee_supervisor' => 1,
            'subscription_types' => array_merge(SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES, [SubscriptionTypeEnum::MILITANT_ACTION_SMS]),
            'subscribedTags' => '75,75008,CIRCO_75001',
            'zones' => [
                LoadGeoZoneData::getZone($manager, 'zone_borough_75108'),
                LoadGeoZoneData::getZone($manager, 'zone_district_75-1'),
            ],
            'created_at' => '2017-01-03 08:47:54',
            'gender' => 'male',
            'certified_at' => '2017-02-01 10:20:45',
            'interests' => ['europe', 'numerique', 'sante'],
            'cotisation_dates' => ['2022-02-01 12:00:00', '2023-03-01 12:00:00'],
            'tags' => $adherent->tags,
            'additional_tags' => ['new_adherent', 'donator_n-x'],
        ]);

        /** @var Adherent $adherent */
        $adherent = $this->getReference('deputy-75-1');
        $managedUser6 = $managedUserFactory->createFromArray([
            'status' => ManagedUser::STATUS_READY,
            'source' => null,
            'original_id' => $adherent->getId(),
            'uuid' => $adherent->getUuid(),
            'email' => $adherent->getEmailAddress(),
            'postal_code' => $adherent->getPostalCode(),
            'address' => $adherent->getAddress(),
            'city' => $adherent->getCityName(),
            'country' => $adherent->getCountry(),
            'first_name' => $adherent->getFirstName(),
            'last_name' => $adherent->getLastName(),
            'birthday' => $adherent->getBirthdate(),
            'is_committee_member' => 0,
            'is_committee_host' => 0,
            'is_committee_provisional_supervisor' => 0,
            'is_committee_supervisor' => 0,
            'subscription_types' => array_merge(SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES, [SubscriptionTypeEnum::MILITANT_ACTION_SMS]),
            'subscribedTags' => '75,75008,CIRCO_75001',
            'zones' => [
                LoadGeoZoneData::getZone($manager, 'zone_borough_75108'),
                LoadGeoZoneData::getZone($manager, 'zone_district_75-1'),
            ],
            'created_at' => '2017-06-01 09:26:31',
            'gender' => 'male',
            'certified_at' => '2017-06-01 17:55:45',
            'interests' => ['europe', 'numerique'],
            'cotisation_dates' => ['2022-01-01 12:00:00', '2023-01-01 12:00:00'],
            'tags' => $adherent->tags,
            'additional_tags' => ['new_adherent', 'old_adherent_em', 'donator_n', 'donator_n-x'],
        ]);

        /** @var Adherent $adherent */
        $adherent = $this->getReference('correspondent-1');
        $manager->persist($managedUserFactory->createFromArray([
            'status' => ManagedUser::STATUS_READY,
            'source' => MembershipSourceEnum::JEMENGAGE,
            'original_id' => $adherent->getId(),
            'uuid' => $adherent->getUuid(),
            'email' => $adherent->getEmailAddress(),
            'postal_code' => $adherent->getPostalCode(),
            'address' => $adherent->getAddress(),
            'city' => $adherent->getCityName(),
            'country' => $adherent->getCountry(),
            'first_name' => $adherent->getFirstName(),
            'last_name' => $adherent->getLastName(),
            'gender' => $adherent->getGender(),
            'birthday' => $adherent->getBirthdate(),
            'zones' => [
               LoadGeoZoneData::getZone($manager, 'zone_department_92'), // Hauts-de-Seine
               LoadGeoZoneData::getZone($manager, 'zone_department_59'), // Nord
            ],
            'created_at' => '2017-06-02 15:34:12',
            'declared_mandates' => [MandateTypeEnum::DEPUTE_EUROPEEN, MandateTypeEnum::CONSEILLER_MUNICIPAL],
            'tags' => $adherent->tags,
        ]));

        $manager->persist($managedUser1);
        $manager->persist($managedUser2);
        $manager->persist($managedUser3);
        $manager->persist($managedUser4);
        $manager->persist($managedUser5);
        $manager->persist($managedUser6);

        $manager->flush();
    }

    private function getManagedUserFactory(): ManagedUserFactory
    {
        return new ManagedUserFactory();
    }

    public function getDependencies()
    {
        return [
            LoadCommitteeV1Data::class,
            LoadGeoZoneData::class,
        ];
    }
}

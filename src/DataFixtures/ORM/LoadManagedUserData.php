<?php

namespace App\DataFixtures\ORM;

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

        $managedUser1 = $managedUserFactory->createFromArray([
            'status' => ManagedUser::STATUS_READY,
            'source' => null,
            'original_id' => $this->getReference('adherent-1')->getId(),
            'uuid' => $this->getReference('adherent-1')->getUuid(),
            'email' => $this->getReference('adherent-1')->getEmailAddress(),
            'postal_code' => $this->getReference('adherent-1')->getPostalCode(),
            'address' => $this->getReference('adherent-1')->getAddress(),
            'city' => $this->getReference('adherent-1')->getCityName(),
            'country' => $this->getReference('adherent-1')->getCountry(),
            'first_name' => $this->getReference('adherent-1')->getFirstName(),
            'last_name' => $this->getReference('adherent-1')->getLastName(),
            'birthday' => $this->getReference('adherent-1')->getBirthdate(),
            'phone' => PhoneNumberUtils::create('+33666666666'),
            'is_committee_member' => 0,
            'is_committee_host' => 0,
            'is_committee_provisional_supervisor' => 0,
            'is_committee_supervisor' => 0,
            'subscription_types' => [SubscriptionTypeEnum::MILITANT_ACTION_SMS],
            'subscribedTags' => 'ch',
            'zones' => [
                LoadGeoZoneData::getZoneReference($manager, 'zone_country_CH'), // Suisse
            ],
            'created_at' => '2017-06-01 09:22:45',
            'gender' => 'male',
            'certified_at' => '2018-06-01 10:20:45',
            'interests' => ['europe', 'numerique', 'sante'],
        ]);

        $managedUser2 = $managedUserFactory->createFromArray([
            'status' => ManagedUser::STATUS_READY,
            'source' => null,
            'original_id' => $this->getReference('adherent-13')->getId(),
            'uuid' => $this->getReference('adherent-13')->getUuid(),
            'email' => $this->getReference('adherent-13')->getEmailAddress(),
            'postal_code' => $this->getReference('adherent-13')->getPostalCode(),
            'address' => $this->getReference('adherent-13')->getAddress(),
            'city' => $this->getReference('adherent-13')->getCityName(),
            'country' => $this->getReference('adherent-13')->getCountry(),
            'first_name' => $this->getReference('adherent-13')->getFirstName(),
            'last_name' => $this->getReference('adherent-13')->getLastName(),
            'birthday' => $this->getReference('adherent-13')->getBirthdate(),
            'committees' => $committee10->getName(),
            'committee_uuids' => [$committee10->getUuid()->toString()],
            'phone' => PhoneNumberUtils::create('+33666666666'),
            'is_committee_member' => 1,
            'is_committee_host' => 0,
            'is_committee_provisional_supervisor' => 0,
            'is_committee_supervisor' => 0,
            'subscription_types' => [SubscriptionTypeEnum::REFERENT_EMAIL, SubscriptionTypeEnum::MILITANT_ACTION_SMS],
            'subscribedTags' => 'ch',
            'zones' => [
                LoadGeoZoneData::getZoneReference($manager, 'zone_country_CH'), // Suisse
            ],
            'created_at' => '2017-06-02 15:34:12',
            'gender' => 'male',
            'interests' => ['numerique'],
        ]);

        $managedUser3 = $managedUserFactory->createFromArray([
            'status' => ManagedUser::STATUS_READY,
            'source' => null,
            'original_id' => $this->getReference('adherent-5')->getId(),
            'uuid' => $this->getReference('adherent-5')->getUuid(),
            'email' => $this->getReference('adherent-5')->getEmailAddress(),
            'postal_code' => $this->getReference('adherent-5')->getPostalCode(),
            'address' => $this->getReference('adherent-5')->getAddress(),
            'city' => $this->getReference('adherent-5')->getCityName(),
            'country' => $this->getReference('adherent-5')->getCountry(),
            'first_name' => $this->getReference('adherent-5')->getFirstName(),
            'last_name' => $this->getReference('adherent-5')->getLastName(),
            'birthday' => $this->getReference('adherent-5')->getBirthdate(),
            'committees' => $committee1->getName(),
            'committee_uuids' => [$committee1->getUuid()->toString()],
            'phone' => PhoneNumberUtils::create('+33666666666'),
            'is_committee_member' => 1,
            'is_committee_host' => 1,
            'is_committee_provisional_supervisor' => 1,
            'is_committee_supervisor' => 0,
            'subscription_types' => [SubscriptionTypeEnum::REFERENT_EMAIL, SubscriptionTypeEnum::MILITANT_ACTION_SMS],
            'subscribedTags' => '92,59',
            'zones' => [
                LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'), // Hauts-de-Seine
                LoadGeoZoneData::getZoneReference($manager, 'zone_city_92024'), // Clichy
            ],
            'created_at' => '2017-06-02 15:34:12',
            'gender' => 'female',
            'certified_at' => '2018-06-02 10:20:45',
        ]);

        $managedUser4 = $managedUserFactory->createFromArray([
            'status' => ManagedUser::STATUS_READY,
            'source' => null,
            'original_id' => $this->getReference('adherent-7')->getId(),
            'uuid' => $this->getReference('adherent-7')->getUuid(),
            'email' => $this->getReference('adherent-7')->getEmailAddress(),
            'postal_code' => $this->getReference('adherent-7')->getPostalCode(),
            'committee_postal_code' => '91',
            'address' => $this->getReference('adherent-7')->getAddress(),
            'city' => $this->getReference('adherent-7')->getCityName(),
            'country' => $this->getReference('adherent-7')->getCountry(),
            'first_name' => $this->getReference('adherent-7')->getFirstName(),
            'last_name' => $this->getReference('adherent-7')->getLastName(),
            'birthday' => $this->getReference('adherent-7')->getBirthdate(),
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
                LoadGeoZoneData::getZoneReference($manager, 'zone_district_77-1'), // Seine-et-Marne (1)
                LoadGeoZoneData::getZoneReference($manager, 'zone_city_77288'), // Melun
            ],
            'created_at' => '2017-08-12 16:12:13',
            'gender' => 'male',
            'supervisor_tags' => [
                '77',
            ],
        ]);

        $managedUser5 = $managedUserFactory->createFromArray([
            'status' => ManagedUser::STATUS_READY,
            'source' => null,
            'original_id' => $this->getReference('adherent-3')->getId(),
            'uuid' => $this->getReference('adherent-3')->getUuid(),
            'email' => $this->getReference('adherent-3')->getEmailAddress(),
            'postal_code' => $this->getReference('adherent-3')->getPostalCode(),
            'address' => $this->getReference('adherent-3')->getAddress(),
            'city' => $this->getReference('adherent-3')->getCityName(),
            'country' => $this->getReference('adherent-3')->getCountry(),
            'first_name' => $this->getReference('adherent-3')->getFirstName(),
            'last_name' => $this->getReference('adherent-3')->getLastName(),
            'birthday' => $this->getReference('adherent-3')->getBirthdate(),
            'phone' => PhoneNumberUtils::create('+33187264236'),
            'is_committee_member' => 1,
            'is_committee_host' => 1,
            'is_committee_provisional_supervisor' => 0,
            'is_committee_supervisor' => 1,
            'subscription_types' => array_merge(SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES, [SubscriptionTypeEnum::MILITANT_ACTION_SMS]),
            'subscribedTags' => '75,75008,CIRCO_75001',
            'zones' => [
                LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75108'),
                LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'),
            ],
            'created_at' => '2017-01-03 08:47:54',
            'gender' => 'male',
            'certified_at' => '2017-02-01 10:20:45',
            'interests' => ['europe', 'numerique', 'sante'],
        ]);

        $managedUser6 = $managedUserFactory->createFromArray([
            'status' => ManagedUser::STATUS_READY,
            'source' => null,
            'original_id' => $this->getReference('deputy-75-1')->getId(),
            'uuid' => $this->getReference('deputy-75-1')->getUuid(),
            'email' => $this->getReference('deputy-75-1')->getEmailAddress(),
            'postal_code' => $this->getReference('deputy-75-1')->getPostalCode(),
            'address' => $this->getReference('deputy-75-1')->getAddress(),
            'city' => $this->getReference('deputy-75-1')->getCityName(),
            'country' => $this->getReference('deputy-75-1')->getCountry(),
            'first_name' => $this->getReference('deputy-75-1')->getFirstName(),
            'last_name' => $this->getReference('deputy-75-1')->getLastName(),
            'birthday' => $this->getReference('deputy-75-1')->getBirthdate(),
            'is_committee_member' => 0,
            'is_committee_host' => 0,
            'is_committee_provisional_supervisor' => 0,
            'is_committee_supervisor' => 0,
            'subscription_types' => array_merge(SubscriptionTypeEnum::DEFAULT_EMAIL_TYPES, [SubscriptionTypeEnum::MILITANT_ACTION_SMS]),
            'subscribedTags' => '75,75008,CIRCO_75001',
            'zones' => [
                LoadGeoZoneData::getZoneReference($manager, 'zone_borough_75108'),
                LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'),
            ],
            'created_at' => '2017-06-01 09:26:31',
            'gender' => 'male',
            'certified_at' => '2017-06-01 17:55:45',
            'interests' => ['europe', 'numerique'],
        ]);

        $manager->persist($managedUserFactory->createFromArray([
            'status' => ManagedUser::STATUS_READY,
            'source' => MembershipSourceEnum::JEMENGAGE,
            'original_id' => ($user = $this->getReference('correspondent-1'))->getId(),
            'uuid' => $user->getUuid(),
            'email' => $user->getEmailAddress(),
            'postal_code' => $user->getPostalCode(),
            'address' => $user->getAddress(),
            'city' => $user->getCityName(),
            'country' => $user->getCountry(),
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName(),
            'gender' => $user->getGender(),
            'birthday' => $user->getBirthdate(),
            'zones' => [
               LoadGeoZoneData::getZoneReference($manager, 'zone_department_92'), // Hauts-de-Seine
               LoadGeoZoneData::getZoneReference($manager, 'zone_department_59'), // Nord
            ],
            'created_at' => '2017-06-02 15:34:12',
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
            LoadCommitteeData::class,
            LoadGeoZoneData::class,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Geo\Zone;
use App\Entity\LegislativeNewsletterSubscription;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadLegislativeNewsletterSubscriptionData extends Fixture implements DependentFixtureInterface
{
    public const LEGISLATIVE_NEWSLETTER_SUBSCRIPTION_1_UUID = 'b9189770-42d5-439a-bd26-52a9cf9ffd1f';
    public const LEGISLATIVE_NEWSLETTER_SUBSCRIPTION_2_UUID = '9e0e1a9e-2c5d-4d3a-a6c8-4a582ff78e22';
    public const LEGISLATIVE_NEWSLETTER_SUBSCRIPTION_2_TOKEN = 'bd879a23-43bf-47d2-b67b-9c7cbb085547';

    public function load(ObjectManager $manager): void
    {
        $newsletterSubscription758 = $this->createSubscription(
            self::LEGISLATIVE_NEWSLETTER_SUBSCRIPTION_1_UUID,
            'john@example.org',
            '75008',
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-1'),
            'John',
            new \DateTime('-3 hours'),
            true
        );

        $newsletterSubscription759 = $this->createSubscription(
            self::LEGISLATIVE_NEWSLETTER_SUBSCRIPTION_2_UUID,
            'jane@example.org',
            '75008',
            LoadGeoZoneData::getZoneReference($manager, 'zone_district_75-9'),
            null,
            null,
            true
        );
        $newsletterSubscription759->setToken(Uuid::fromString(self::LEGISLATIVE_NEWSLETTER_SUBSCRIPTION_2_TOKEN));

        $manager->persist($newsletterSubscription758);
        $manager->persist($newsletterSubscription759);

        $manager->flush();
    }

    public function createSubscription(
        string $uuid,
        string $emailAddress,
        string $postalCode,
        Zone $zone,
        ?string $firstName,
        ?\DateTimeInterface $dateTime = null,
        bool $personalDataCollection = false,
    ): LegislativeNewsletterSubscription {
        $subscription = new LegislativeNewsletterSubscription(Uuid::fromString($uuid));
        $subscription->setEmailAddress($emailAddress);
        $subscription->setPostalCode($postalCode);
        $subscription->addFromZone($zone);
        $subscription->setFirstName($firstName);
        $subscription->setConfirmedAt($dateTime);
        $subscription->setPersonalDataCollection($personalDataCollection);

        return $subscription;
    }

    public function getDependencies(): array
    {
        return [
            LoadGeoZoneData::class,
        ];
    }
}

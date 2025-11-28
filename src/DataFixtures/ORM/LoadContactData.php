<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Contact;
use App\Membership\Contact\SourceEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadContactData extends Fixture
{
    public const CONTACT_1_UUID = 'fdbc1c47-2c2e-4caf-b9d7-1212cabcd26f';

    public function load(ObjectManager $manager): void
    {
        $manager->persist($this->createContact(
            self::CONTACT_1_UUID,
            'Rémi',
            'remi@avecvous.dev',
            SourceEnum::AVECVOUS
        ));

        $manager->flush();
    }

    private function createContact(string $uuid, string $firstName, string $email, string $source): Contact
    {
        $contact = new Contact(
            Uuid::fromString($uuid),
            $firstName,
            $email,
            $source
        );

        $contact->setCguAccepted(true);

        return $contact;
    }
}

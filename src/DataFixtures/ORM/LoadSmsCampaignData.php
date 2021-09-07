<?php

namespace App\DataFixtures\ORM;

use App\Entity\Audience\AudienceSnapshot;
use App\Entity\SmsCampaign;
use App\SmsCampaign\SmsCampaignStatusEnum;
use App\ValueObject\Genders;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadSmsCampaignData extends Fixture implements DependentFixtureInterface
{
    public const UUID_1 = '0950cc74-eb81-49c6-9989-8500530feaa6';
    public const UUID_2 = 'a8a9b003-a05c-40b9-bc78-8184a2a4ac71';
    public const UUID_3 = '78a6625c-9d4a-4bfa-b9b7-f5a3a641b312';
    public const UUID_4 = '3eae8273-e51d-404f-8668-dbfd50eaff19';

    public function load(ObjectManager $manager)
    {
        $manager->persist($object = new SmsCampaign(Uuid::fromString(self::UUID_1)));
        $object->setTitle('Campagne en brouillon');
        $object->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
        $object->setRecipientCount(42);
        $object->setAudience($audience = new AudienceSnapshot());
        $audience->setAgeMax(27);
        $audience->setAgeMin(18);

        $manager->persist($object = new SmsCampaign(Uuid::fromString(self::UUID_2)));
        $object->setTitle('Campagne en cours d\'envoi');
        $object->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
        $object->setRecipientCount(35);
        $object->send();
        $object->setAudience($audience = new AudienceSnapshot());
        $audience->setGender(Genders::FEMALE);

        $manager->persist($object = new SmsCampaign(Uuid::fromString(self::UUID_3)));
        $object->setTitle('Campagne envoyée');
        $object->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
        $object->setRecipientCount(27);
        $object->setAudience($audience = new AudienceSnapshot());
        $audience->setGender(Genders::MALE);
        $object->setStatus(SmsCampaignStatusEnum::DONE);
        $object->setResponsePayload('{"accountID":123,"createdAt":"2021-09-06T18:08:52+02:00","errors":[],"estimatedCredits":1,"finishedAt":null,"from":"En Marche","id":"15b93293-d257-4787-baf5-f3810fc23c6b","status":"PENDING","totalRecords":1,"updatedAt":"2021-09-06T18:08:52+02:00"}');
        $object->setExternalId('e4712f5b-34fc-4644-9873-2dc19fd4d263');

        $manager->persist($object = new SmsCampaign(Uuid::fromString(self::UUID_4)));
        $object->setTitle('Campagne en erreur');
        $object->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
        $object->setRecipientCount(20);
        $object->setAudience($audience = new AudienceSnapshot());
        $audience->setGender(Genders::MALE);
        $object->setStatus(SmsCampaignStatusEnum::ERROR);
        $object->setResponsePayload('{"class":"Client::BadRequest","message":"Unknown body parameter sender"}');

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdminData::class,
        ];
    }
}

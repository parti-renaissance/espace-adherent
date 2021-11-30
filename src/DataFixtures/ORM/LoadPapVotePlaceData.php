<?php

namespace App\DataFixtures\ORM;

use App\Entity\Pap\VotePlace;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class LoadPapVotePlaceData extends Fixture
{
    private const UUID_1 = 'dcaec65c-0856-4c27-adf5-6d51593601e3';
    private const UUID_2 = '8788d1df-9807-45db-a79a-3e1c03df141b';

    public function load(ObjectManager $manager)
    {
        $manager->persist($object = new VotePlace(
            48.8589506,
            2.2773447,
            Uuid::fromString(self::UUID_1),
        ));
        $this->setReference('pap-vote-place--paris-8-a', $object);

        $manager->persist($object = new VotePlace(
            48.8780275,
            2.3178072,
            Uuid::fromString(self::UUID_2),
        ));
        $this->setReference('pap-vote-place--paris-8-b', $object);

        $manager->flush();
    }
}

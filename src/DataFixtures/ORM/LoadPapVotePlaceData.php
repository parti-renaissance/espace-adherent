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
    private const UUID_3 = '7157a379-e66d-4afd-b1a3-412fbf9ce0e5';
    private const UUID_4 = '1c74d299-0f95-4d14-8990-713b57713ebd';
    private const UUID_5 = '8daa4d93-4881-42b3-9e0b-5e6324828a62';
    private const UUID_6 = '33106ef9-ba14-4281-8032-e186735df717';
    private const UUID_7 = '9ece4e07-0c46-4e94-a0d0-087efbe30fff';
    private const UUID_8 = '3e254a91-9779-4ccd-96a5-bc19f8b9579d';
    private const UUID_9 = 'aac8bf0d-aa66-4860-a7ed-dbfe85ed544f';
    private const UUID_10 = '3db888e3-147f-4334-b2b3-16eff68a23c9';

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
            0.03448712620899716,
            -0.04701780333257613,
            0.1924375422012154,
            1
        ));
        $this->setReference('pap-vote-place--paris-8-b', $object);

        $manager->persist($object = new VotePlace(
            48.822865,
            2.362221,
            Uuid::fromString(self::UUID_3),
            -0.11020033706766413,
            0.09149141048645792,
            0.1564625850340136,
            2
        ));
        $this->setReference('pap-vote-place--paris-3-b', $object);

        $manager->persist($object = new VotePlace(
            48.75202,
            2.293244,
            Uuid::fromString(self::UUID_4),
            -0.01368787135603089,
            0.08377306353576569,
            0.1395582329317269,
            1
        ));
        $this->setReference('pap-vote-place--anthony-a', $object);

        $manager->persist($object = new VotePlace(
            48.760128,
            2.297235,
            Uuid::fromString(self::UUID_5),
            -0.02095552502504222,
            0.08456851575224938,
            0.14551804423748546,
            1
        ));
        $this->setReference('pap-vote-place--anthony-b', $object);

        $manager->persist($object = new VotePlace(
            48.75752,
            2.304083,
            Uuid::fromString(self::UUID_6),
            -0.06196240784844481,
            0.09086137737347927,
            0.14814814814814814,
            1
        ));
        $this->setReference('pap-vote-place--anthony-c', $object);

        $manager->persist($object = new VotePlace(
            48.94159,
            2.157997,
            Uuid::fromString(self::UUID_7),
            -0.050389164528078456,
            0.07462764506078307,
            0.1825876662636034,
            1
        ));
        $this->setReference('pap-vote-place--sartrouville-a', $object);

        $manager->persist($object = new VotePlace(
            48.93528,
            2.151656,
            Uuid::fromString(self::UUID_8)
        ));
        $this->setReference('pap-vote-place--sartrouville-b', $object);

        $manager->persist($object = new VotePlace(
            48.934376,
            2.155423,
            Uuid::fromString(self::UUID_9)
        ));
        $this->setReference('pap-vote-place--sartrouville-c', $object);

        $manager->persist($object = new VotePlace(
            48.348328,
            2.561779,
            Uuid::fromString(self::UUID_10),
            0.027060904596480972,
            0.01574765192935429,
            0.17720090293453725,
            1
        ));
        $this->setReference('pap-vote-place--achere-la-forêt-a', $object);

        $manager->flush();
    }
}

<?php

namespace Tests\AppBundle\Membership\EventListener;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\PostAddress;
use AppBundle\Membership\EventListener\UserSubscriber;
use AppBundle\Membership\UserEvent;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserSubscriberTest extends KernelTestCase
{
    public function testSerialiseUser()
    {
        self::bootKernel();
        $serializer = self::$kernel->getContainer()->get('jms_serializer');

        $producer = $this->getMockBuilder(ProducerInterface::class)->getMock();
        $userSubscriber = new UserSubscriber($producer, $serializer);

        $user = Adherent::create(
            Uuid::fromString('a046adbe-9c7b-56a9-a676-6151a6785dda'),
            'jacques.picard@en-marche.fr',
            'password',
            'male',
            'Jacques',
            'Picard',
            new \DateTime(),
            'foo',
            PostAddress::createFrenchAddress('36 rue de la Paix', '75008-75108', null, 48.8699464, 2.3297187)
        );

        $this->assertSame(
            '{"uuid":"a046adbe-9c7b-56a9-a676-6151a6785dda","subscriptionExternalIds":[],"country":"FR","zipCode":"75008","tags":[],"emailAddress":"jacques.picard@en-marche.fr","firstName":"Jacques","lastName":"Picard"}',
            $userSubscriber->serialize(new UserEvent($user))
        );
    }
}

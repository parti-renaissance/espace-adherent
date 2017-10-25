<?php

namespace Tests\AppBundle\EventListener;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\EventListener\RegistrationCompletedSubscriber;
use AppBundle\Membership\AdherentAccountWasCreatedEvent;
use AppBundle\Membership\MembershipRequest;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use PHPUnit\Framework\TestCase;

class RegistrationCompletedSubscriberTest extends TestCase
{
    public function test_it_send_a_message()
    {
        $expectedParams = \GuzzleHttp\json_encode([
            'emailAddress' => 'foo.bar@example.com',
            'firstName' => 'Foo',
            'lastName' => 'Bar',
            'zipCode' => '59000',
            'plainPassword' => 'password',
        ]);

        $producer = $this->getMockBuilder(ProducerInterface::class)->getMock();
        $producer->expects($this->once())->method('publish')->with($this->equalTo($expectedParams));

        $membershipRequest = new MembershipRequest();
        $membershipRequest->setEmailAddress('foo.bar@example.com');
        $membershipRequest->firstName = 'Foo';
        $membershipRequest->lastName = 'Bar';
        $membershipRequest->password = 'password';
        $address = new Address();
        $address->setPostalCode('59000');
        $membershipRequest->setAddress($address);

        (new RegistrationCompletedSubscriber($producer))->synchroniseWithAuth(new AdherentAccountWasCreatedEvent(
            $this->getMockBuilder(Adherent::class)->disableOriginalConstructor()->getMock(),
            $membershipRequest
        ));
    }
}

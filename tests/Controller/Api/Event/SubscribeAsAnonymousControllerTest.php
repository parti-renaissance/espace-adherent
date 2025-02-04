<?php

namespace Tests\App\Controller\Api\Event;

use App\DataFixtures\ORM\LoadCommitteeEventData;
use App\DataFixtures\ORM\LoadEventData;
use App\Mailer\Message\Renaissance\EventRegistrationConfirmationMessage;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractApiTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

#[Group('functional')]
#[Group('api')]
class SubscribeAsAnonymousControllerTest extends AbstractApiTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    #[DataProvider('provideEvents')]
    public function testAnonymousCanSubscribeOnEvent(string $eventUuid)
    {
        $this->client->request(Request::METHOD_POST, \sprintf('/api/events/%s/subscribe', $eventUuid), [], [], [], json_encode([
            'first_name' => 'Joe',
        ]));

        $this->assertStatusCode(400, $this->client);

        $this->client->request(Request::METHOD_POST, \sprintf('/api/events/%s/subscribe', $eventUuid), [], [], [], json_encode([
            'first_name' => 'Joe',
            'last_name' => 'Hey',
            'email_address' => $email = 'j.hey@en-marche-dev.fr',
        ]));

        $this->isSuccessful($this->client->getResponse());

        $registration = $this->getEventRegistrationRepository()->findGuestRegistration($eventUuid, $email);
        $this->assertSame('Joe', $registration->getFirstName());
        $this->assertSame('Hey', $registration->getLastName());
    }

    #[DataProvider('provideCancelledEvents')]
    public function testAnonymousCannotSubscribeOnCancelledEvent(string $eventUuid, string $messageClass)
    {
        $this->client->request(Request::METHOD_POST, \sprintf('/api/events/%s/subscribe', $eventUuid), [], [], [], json_encode([
            'first_name' => 'Joe',
            'last_name' => 'Hey',
            'email_address' => 'j.hey@en-marche-dev.fr',
        ]));

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
        $this->assertCountMails(0, $messageClass, 'j.hey@en-marche-dev.fr');
    }

    #[DataProvider('providePrivateEvents')]
    public function testAnonymousCannotSubscribeOnPrivateEvent(string $eventUuid, string $messageClass)
    {
        $this->client->request(Request::METHOD_POST, \sprintf('/api/events/%s/subscribe', $eventUuid), [], [], [], json_encode([
            'first_name' => 'Joe',
            'last_name' => 'Hey',
            'email_address' => 'j.hey@en-marche-dev.fr',
        ]));

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
        $this->assertCountMails(0, $messageClass, 'j.hey@en-marche-dev.fr');
    }

    public static function provideEvents(): iterable
    {
        yield [LoadEventData::EVENT_1_UUID];
    }

    public static function provideCancelledEvents(): iterable
    {
        yield [LoadCommitteeEventData::EVENT_6_UUID, EventRegistrationConfirmationMessage::class];
    }

    public static function providePrivateEvents(): iterable
    {
        yield [LoadCommitteeEventData::EVENT_3_UUID, EventRegistrationConfirmationMessage::class];
    }
}

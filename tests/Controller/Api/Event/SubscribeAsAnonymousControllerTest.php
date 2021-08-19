<?php

namespace Tests\App\Controller\Api\Event;

use App\DataFixtures\ORM\LoadCauseEventData;
use App\DataFixtures\ORM\LoadCoalitionEventData;
use App\DataFixtures\ORM\LoadCommitteeEventData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ApiControllerTestTrait;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group api
 */
class SubscribeAsAnonymousControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use ApiControllerTestTrait;

    /** @dataProvider provideEvents */
    public function testAnonymousCanSubscribeOnEvent(string $eventUuid, string $messageClass)
    {
        $this->client->request(Request::METHOD_POST, sprintf('/api/events/%s/subscribe', $eventUuid), [], [], [], json_encode([
            'first_name' => 'Joe',
        ]));

        $this->assertStatusCode(400, $this->client);

        $this->client->request(Request::METHOD_POST, sprintf('/api/events/%s/subscribe', $eventUuid), [], [], [], json_encode([
            'first_name' => 'Joe',
            'last_name' => 'Hey',
            'email_address' => $email = 'j.hey@en-marche-dev.fr',
        ]));

        $this->isSuccessful($this->client->getResponse());
        $this->assertCountMails(1, $messageClass, 'j.hey@en-marche-dev.fr');

        $registration = $this->getEventRegistrationRepository()->findGuestRegistration($eventUuid, $email);
        $this->assertSame('Joe', $registration->getFirstName());
        $this->assertSame('Hey', $registration->getLastName());
    }

    /** @dataProvider provideCancelledEvents */
    public function testAnonymousCannotSubscribeOnCancelledEvent(string $eventUuid, string $messageClass)
    {
        $this->client->request(Request::METHOD_POST, sprintf('/api/events/%s/subscribe', $eventUuid), [], [], [], json_encode([
            'first_name' => 'Joe',
            'last_name' => 'Hey',
            'email_address' => 'j.hey@en-marche-dev.fr',
        ]));

        $this->assertStatusCode(Response::HTTP_NOT_FOUND, $this->client);
        $this->assertCountMails(0, $messageClass, 'j.hey@en-marche-dev.fr');
    }

    public function provideEvents(): iterable
    {
        yield [LoadCoalitionEventData::EVENT_1_UUID, 'CoalitionsEventRegistrationConfirmationMessage'];
        yield [LoadCauseEventData::EVENT_1_UUID, 'CoalitionsEventRegistrationConfirmationMessage'];
        yield [LoadCommitteeEventData::EVENT_1_UUID, 'EventRegistrationConfirmationMessage'];
    }

    public function provideCancelledEvents(): iterable
    {
        yield [LoadCoalitionEventData::EVENT_6_UUID, 'CoalitionsEventRegistrationConfirmationMessage'];
        yield [LoadCauseEventData::EVENT_4_UUID, 'CoalitionsEventRegistrationConfirmationMessage'];
        yield [LoadCommitteeEventData::EVENT_6_UUID, 'EventRegistrationConfirmationMessage'];
    }
}

<?php

namespace Tests\AppBundle\EventListener;

use AppBundle\Entity\Event;
use AppBundle\Entity\EventCategory;
use AppBundle\Entity\EventRegistration;
use AppBundle\Entity\PostAddress;
use AppBundle\Event\EventRegistrationEvent;
use AppBundle\Event\EventRegistrationSubscriber;
use AppBundle\Mailer\MailerService;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EventRegistrationSubscriberTest extends TestCase
{
    private const REGISTRATION_UUID = '75aac96a-9cba-4bd8-91f4-414d269ca0b0';
    private const EVENT_UUID = 'a103e823-1bd1-406d-81ec-d1f764437d1b';
    private const EVENT_SLUG = '/foobar-slug';

    /** @var MailerService */
    private $mailer;

    private $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailer = $this->createMock(MailerService::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
    }

    protected function tearDown(): void
    {
        $this->mailer = null;
        $this->urlGenerator = null;

        parent::tearDown();
    }

    /**
     * @dataProvider provideEventRegistrationCreated
     */
    public function testSendRegistrationEmail(?EventRegistration $registration, bool $sendMail)
    {
        $eventSubscriber = new EventRegistrationSubscriber($this->mailer, $this->urlGenerator);

        $eventRegistrationEvent = new EventRegistrationEvent(
            $registration,
            self::EVENT_SLUG,
            $sendMail
        );

        $this->mailer
            ->expects($sendMail ? $this->once() : $this->never())
            ->method('sendMessage')
        ;

        $this->urlGenerator
            ->expects($sendMail ? $this->once() : $this->never())
            ->method('generate')
            ->with('app_event_show', ['slug' => self::EVENT_SLUG], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('/url')
        ;

        $eventSubscriber->sendRegistrationEmail($eventRegistrationEvent);
    }

    public function provideEventRegistrationCreated(): iterable
    {
        yield [$this->createRegistration(), true];
        yield [$this->createRegistration(), false];
    }

    private function createRegistration(): EventRegistration
    {
        return new EventRegistration(
            Uuid::fromString(self::REGISTRATION_UUID),
            $this->createEvent(self::EVENT_UUID),
            'Bonsoir',
            'foo@bar.tld',
            '59000',
            true
        );
    }

    private function createEvent(string $uuid): Event
    {
        return new Event(
            Uuid::fromString($uuid),
            null,
            null,
            'My name',
            new EventCategory(),
            'My description',
            PostAddress::createFrenchAddress('street', '59000-59000'),
            '2017-01-01',
            '2017-01-04'
        );
    }
}

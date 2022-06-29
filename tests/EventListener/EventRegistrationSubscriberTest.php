<?php

namespace Tests\App\EventListener;

use App\Coalition\CoalitionUrlGenerator;
use App\Entity\Event\CommitteeEvent;
use App\Entity\Event\EventCategory;
use App\Entity\Event\EventRegistration;
use App\Event\EventRegistrationEvent;
use App\Event\EventRegistrationSubscriber;
use App\Mailer\MailerService;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tests\App\AbstractKernelTestCase;

class EventRegistrationSubscriberTest extends AbstractKernelTestCase
{
    private const REGISTRATION_UUID = '75aac96a-9cba-4bd8-91f4-414d269ca0b0';
    private const EVENT_UUID = 'a103e823-1bd1-406d-81ec-d1f764437d1b';
    private const EVENT_SLUG = '/foobar-slug';

    private ?MailerService $mailer;
    private $urlGenerator;
    private $coalitionUrlGenerator;

    /**
     * @dataProvider provideEventRegistrationCreated
     */
    public function testSendRegistrationEmail(bool $sendMail)
    {
        $eventSubscriber = new EventRegistrationSubscriber($this->mailer, $this->urlGenerator, $this->coalitionUrlGenerator);

        $eventRegistrationEvent = new EventRegistrationEvent(
            $this->createRegistration(),
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
            ->with('app_committee_event_show', ['slug' => self::EVENT_SLUG], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('/url')
        ;

        $eventSubscriber->sendRegistrationEmail($eventRegistrationEvent);
    }

    public function provideEventRegistrationCreated(): array
    {
        return [
            [true],
            [false],
        ];
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

    private function createEvent(string $uuid): CommitteeEvent
    {
        return new CommitteeEvent(
            Uuid::fromString($uuid),
            null,
            null,
            'My name',
            new EventCategory(),
            'My description',
            $this->createPostAddress('street', '59000-59000'),
            '2017-01-01',
            '2017-01-04'
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->mailer = $this->createMock(MailerService::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->coalitionUrlGenerator = $this->createMock(CoalitionUrlGenerator::class);
    }

    protected function tearDown(): void
    {
        $this->mailer = null;
        $this->urlGenerator = null;
        $this->coalitionUrlGenerator = null;

        parent::tearDown();
    }
}

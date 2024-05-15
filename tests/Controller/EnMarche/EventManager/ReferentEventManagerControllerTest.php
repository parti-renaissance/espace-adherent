<?php

namespace Tests\App\Controller\EnMarche\EventManager;

use App\Entity\Event\BaseEvent;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;

class ReferentEventManagerControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;

    private $eventRepository;

    public function testListEvents(): void
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/espace-referent/evenements');

        $this->assertStringContainsString('Nouvel événement online privé et électoral', $crawler->filter('tbody tr.event__item')->eq(0)->text());
        $this->assertStringContainsString('Nouvel événement online', $crawler->filter('tbody tr.event__item')->eq(1)->text());
        $this->assertStringContainsString('Un événement du référent annulé', $crawler->filter('tbody tr.event__item')->eq(2)->text());
        $this->assertStringContainsString('Réunion de réflexion marseillaise', $crawler->filter('tbody tr.event__item')->eq(3)->text());
        $this->assertStringContainsString('Réunion de réflexion dammarienne', $crawler->filter('tbody tr.event__item')->eq(4)->text());
        $this->assertStringContainsString('Réunion de réflexion bellifontaine', $crawler->filter('tbody tr.event__item')->eq(5)->text());
        $this->assertStringContainsString('Event of non AL', $crawler->filter('tbody tr.event__item')->eq(6)->text());
        $this->assertStringContainsString('Événements à Fontainebleau 2', $crawler->filter('tbody tr.event__item')->eq(7)->text());
        $this->assertStringContainsString('Événements à Fontainebleau 1', $crawler->filter('tbody tr.event__item')->eq(8)->text());
        $this->assertStringContainsString('Grand Meeting de Marseille', $crawler->filter('tbody tr.event__item')->eq(9)->text());
    }

    public function testCreateEvent(): void
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/espace-referent/evenements/creer');
        $this->assertResponseIsSuccessful();

        $crawler = $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'event_command[name]' => 'My new referent event',
            'event_command[category]' => 1,
            'event_command[address][address]' => '92 boulevard victor hugo',
            'event_command[address][postalCode]' => '92110',
            'event_command[address][cityName]' => 'Clichy',
            'event_command[address][country]' => 'FR',
            'event_command[description]' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'event_command[private]' => true,
        ]));
        $this->assertResponseIsSuccessful();
        $errors = $crawler->filter('.form__errors > li');
        $this->assertCount(0, $errors);

        /** @var BaseEvent $event */
        $event = $this->eventRepository->createQueryBuilder('event')
            ->setMaxResults(1)
            ->orderBy('event.createdAt', 'DESC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
        $this->assertInstanceOf(BaseEvent::class, $event);

        self::assertSame('My new referent event', $event->getName());
        self::assertSame('Lorem ipsum dolor sit amet, consectetur adipiscing elit.', $event->getDescription());
        self::assertTrue($event->isPrivate());
        self::assertFalse($event->isElectoral());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventRepository = $this->getRepository(BaseEvent::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->eventRepository = null;
    }
}

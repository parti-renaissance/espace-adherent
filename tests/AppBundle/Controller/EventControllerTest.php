<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\Entity\EventRegistration;
use AppBundle\Repository\EventRegistrationRepository;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\SqliteWebTestCase;

class EventControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /** @var EventRegistrationRepository */
    private $repository;

    /**
     * @group functionnal
     */
    public function testAnonymousUserCanRegisterToEvent()
    {
        $committeeUrl = sprintf('/comites/%s/en-marche-paris-8', LoadAdherentData::COMMITTEE_1_UUID);

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('1 inscrit', $crawler->filter('.committee-event-attendees')->text());

        $crawler = $this->client->click($crawler->selectLink('Je participe')->link());
        $crawler = $this->client->click($crawler->selectLink('Je veux participer')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertEmpty($crawler->filter('#field-first-name > input[type="text"]')->attr('value'));
        $this->assertEmpty($crawler->filter('#field-postal-code > input[type="text"]')->attr('value'));
        $this->assertEmpty($crawler->filter('#field-email-address > input[type="email"]')->attr('value'));

        $crawler = $this->client->submit($crawler->selectButton("Je m'inscris")->form());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(3, $crawler->filter('.form__errors')->count());
        $this->assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('#field-first-name .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('#field-postal-code .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('#field-email-address .form__errors > li')->text());

        $this->client->submit($crawler->selectButton("Je m'inscris")->form([
            'event_registration' => [
                'firstName' => 'Pauline',
                'emailAddress' => 'paupau75@example.org',
                'postalCode' => '75001',
                'newsletterSubscriber' => true,
            ],
        ]));

        $this->assertInstanceOf(EventRegistration::class, $this->repository->findGuestRegistration(LoadEventData::EVENT_1_UUID, 'paupau75@example.org'));

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageSuccesfullyCreatedFlash($crawler, "Votre inscription à l'événement est confirmée."));
        $this->assertContains('Votre participation est bien enregistrée !', $crawler->filter('.committee-event-registration-confirmation p')->text());

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('2 inscrits', $crawler->filter('.committee-event-attendees')->text());
    }

    public function testRegisteredAdherentUserCanRegisterToEvent()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');

        $committeeUrl = sprintf('/comites/%s/en-marche-paris-8', LoadAdherentData::COMMITTEE_1_UUID);

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('1 inscrit', $crawler->filter('.committee-event-attendees')->text());

        $crawler = $this->client->click($crawler->selectLink('Je participe')->link());
        $crawler = $this->client->click($crawler->selectLink('Je veux participer')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertSame('Benjamin', $crawler->filter('#field-first-name > input[type="text"]')->attr('value'));
        $this->assertSame('13003', $crawler->filter('#field-postal-code > input[type="text"]')->attr('value'));
        $this->assertSame('benjyd@aol.com', $crawler->filter('#field-email-address > input[type="email"]')->attr('value'));

        $this->client->submit($crawler->selectButton("Je m'inscris")->form());

        $this->assertInstanceOf(EventRegistration::class, $this->repository->findGuestRegistration(LoadEventData::EVENT_1_UUID, 'benjyd@aol.com'));

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageSuccesfullyCreatedFlash($crawler, "Votre inscription à l'événement est confirmée."));
        $this->assertContains('Votre participation est bien enregistrée !', $crawler->filter('.committee-event-registration-confirmation p')->text());

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('2 inscrits', $crawler->filter('.committee-event-attendees')->text());

        $this->client->request(Request::METHOD_GET, '/espace-adherent/mes-evenements');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Réunion de réflexion parisienne', $this->client->getResponse()->getContent());
    }

    /**
     * @group functionnal
     */
    public function testAnonymousUserCannotEditEvent()
    {
        $this->client->request('GET', '/evenements/'.LoadEventData::EVENT_1_UUID.'/2017-02-27-reunion-de-reflexion-parisienne/modifier');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }

    /**
     * @group functionnal
     */
    public function testRegisteredAdherentUserCannotEditEvent()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');

        $this->client->request('GET', '/evenements/'.LoadEventData::EVENT_1_UUID.'/2017-02-27-reunion-de-reflexion-parisienne/modifier');

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    /**
     * @group functionnal
     */
    public function testOrganizerCanEditEvent()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');

        $crawler = $this->client->request('GET', '/evenements/'.LoadEventData::EVENT_1_UUID.'/2017-02-27-reunion-de-reflexion-parisienne/modifier');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'committee_event' => [
                'name' => "Débat sur l'écologie",
                'description' => 'Cette journée sera consacrée à un grand débat sur la question écologique.',
                'category' => 'CE003',
                'address' => [
                    'address' => '6 rue Neyret',
                    'country' => 'FR',
                    'postalCode' => '69001',
                    'city' => '69001-69381',
                    'cityName' => '',
                ],
                'beginAt' => [
                    'date' => [
                        'year' => '2022',
                        'month' => '3',
                        'day' => '2',
                    ],
                    'time' => [
                        'hour' => '9',
                        'minute' => '30',
                    ],
                ],
                'finishAt' => [
                    'date' => [
                        'year' => '2022',
                        'month' => '3',
                        'day' => '2',
                    ],
                    'time' => [
                        'hour' => '19',
                        'minute' => '0',
                    ],
                ],
                'capacity' => '1500',
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        // Follow the redirect and check the adherent can see the committee page
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertContains('L\'événement a bien été modifié.', $crawler->filter('#notice-flashes')->text());
        $this->assertSame('Débat sur l\'écologie | En Marche !', $crawler->filter('title')->text());
        $this->assertSame('Débat sur l\'écologie', $crawler->filter('.committee-event-name')->text());
        $this->assertSame('Organisé par Jacques Picard du comité En Marche Paris 8', trim(preg_replace('/\s+/', ' ', $crawler->filter('.committee-event-organizer')->text())));
        $this->assertSame('Mercredi 2 mars 2022, 9h30', $crawler->filter('.committee-event-date')->text());
        $this->assertSame('6 rue Neyret, 69001 Lyon 1er', $crawler->filter('.committee-event-address')->text());
        $this->assertSame('Cette journée sera consacrée à un grand débat sur la question écologique.', $crawler->filter('.committee-event-description')->text());
    }

    /**
     * @group functionnal
     */
    public function testCommitteeHostCanEditEvent()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $this->client->request('GET', '/evenements/'.LoadEventData::EVENT_1_UUID.'/2017-02-27-reunion-de-reflexion-parisienne/modifier');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    private function seeMessageSuccesfullyCreatedFlash(Crawler $crawler, ?string $message = null)
    {
        $flash = $crawler->filter('#notice-flashes');

        if ($message) {
            $this->assertSame($message, trim($flash->text()));
        }

        return 1 === count($flash);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadEventData::class,
        ]);

        $this->repository = $this->getEventRegistrationRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->repository = null;

        parent::tearDown();
    }
}

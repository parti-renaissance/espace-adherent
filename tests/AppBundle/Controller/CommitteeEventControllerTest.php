<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCommitteeEventData;
use AppBundle\Entity\CommitteeEventRegistration;
use AppBundle\Repository\CommitteeEventRegistrationRepository;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\SqliteWebTestCase;

class CommitteeEventControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /** @var CommitteeEventRegistrationRepository */
    private $repository;

    /**
     * @group functionnal
     */
    public function testAnonymousUserCanRegisterToCommitteeEvent()
    {
        $committeeUrl = sprintf('/comites/%s/en-marche-paris-8', LoadAdherentData::COMMITTEE_1_UUID);

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('1 participant', $crawler->filter('.committee-event-attendees')->text());

        $crawler = $this->client->click($crawler->selectLink('Je participe')->link());

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

        $this->assertInstanceOf(CommitteeEventRegistration::class, $this->repository->findGuestRegistration(LoadCommitteeEventData::COMMITTEE_EVENT_1_UUID, 'paupau75@example.org'));

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageSuccesfullyCreatedFlash($crawler, "Votre inscription à l'événement est confirmée."));
        $this->assertContains('Votre participation est bien enregistrée !', $crawler->filter('.committee-event-registration-confirmation p')->text());

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('2 participants', $crawler->filter('.committee-event-attendees')->text());
    }

    public function testRegisteredAdherentUserCanRegisterToCommitteeEvent()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');

        $committeeUrl = sprintf('/comites/%s/en-marche-paris-8', LoadAdherentData::COMMITTEE_1_UUID);

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('1 participant', $crawler->filter('.committee-event-attendees')->text());

        $crawler = $this->client->click($crawler->selectLink('Je participe')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertSame('Benjamin', $crawler->filter('#field-first-name > input[type="text"]')->attr('value'));
        $this->assertSame('13003', $crawler->filter('#field-postal-code > input[type="text"]')->attr('value'));
        $this->assertSame('benjyd@aol.com', $crawler->filter('#field-email-address > input[type="email"]')->attr('value'));

        $this->client->submit($crawler->selectButton("Je m'inscris")->form());

        $this->assertInstanceOf(CommitteeEventRegistration::class, $this->repository->findGuestRegistration(LoadCommitteeEventData::COMMITTEE_EVENT_1_UUID, 'benjyd@aol.com'));

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageSuccesfullyCreatedFlash($crawler, "Votre inscription à l'événement est confirmée."));
        $this->assertContains('Votre participation est bien enregistrée !', $crawler->filter('.committee-event-registration-confirmation p')->text());

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('2 participants', $crawler->filter('.committee-event-attendees')->text());
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
            LoadCommitteeEventData::class,
        ]);

        $this->repository = $this->getCommitteeEventRegistrationRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->repository = null;

        parent::tearDown();
    }
}

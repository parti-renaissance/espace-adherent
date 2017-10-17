<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenInitiativeCategoryData;
use AppBundle\DataFixtures\ORM\LoadCitizenInitiativeData;
use AppBundle\Mailjet\Message\EventCancellationMessage;
use AppBundle\Mailjet\Message\EventContactMembersMessage;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 * @group citizenInitiativeManager
 */
class CitizenInitiativeManagerControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider provideOrganizerProtectedPages
     */
    public function testAnonymousUserCannotEditCitizenInitiative($path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }

    /**
     * @dataProvider provideCancelledInaccessiblePages
     */
    public function testRegisteredAdherentUserCannotFoundPagesOfCancelledCitizenInitiative($path)
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    /**
     * @dataProvider provideOrganizerProtectedPages
     */
    public function testRegisteredAdherentUserCannotEditCitizenInitiative($path)
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function provideOrganizerProtectedPages()
    {
        $uuid = LoadCitizenInitiativeData::CITIZEN_INITIATIVE_5_UUID;
        $slug = date('Y-m-d', strtotime('+11 days')).'-nettoyage-de-la-kilchberg';

        return [
            ['/initiative-citoyenne/'.$slug.'/modifier'],
            ['/initiative-citoyenne/'.$slug.'/annuler'],
            ['/initiative-citoyenne/'.$slug.'/inscrits'],
        ];
    }

    public function provideCancelledInaccessiblePages()
    {
        $uuid = LoadCitizenInitiativeData::CITIZEN_INITIATIVE_6_UUID;
        $slug = date('Y-m-d', strtotime('+20 days')).'-initiative-citoyenne-annulee';

        return [
            ['/initiative-citoyenne/'.$slug.'/inscription'],
        ];
    }

    public function testOrganizerCanEditCitizenInitiative()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');

        $crawler = $this->client->request('GET', '/initiative-citoyenne/'.date('Y-m-d', strtotime('+11 days')).'-nettoyage-de-la-kilchberg/modifier');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'citizen_initiative' => [
                'name' => 'Nouveau titre',
                'description' => 'Nouvelle description.',
                'category' => $this->getCitizenInitiativeCategoryIdForName(LoadCitizenInitiativeCategoryData::CITIZEN_INITIATIVE_CATEGORIES['CIC006']),
                'address' => [
                    'address' => 'Pilgerweg 58',
                    'country' => 'CH',
                    'postalCode' => '8802',
                    'city' => '',
                    'cityName' => 'Kilchberg',
                ],
                'beginAt' => [
                    'date' => [
                        'year' => '2020',
                        'month' => '3',
                        'day' => '3',
                    ],
                    'time' => [
                        'hour' => '9',
                        'minute' => '30',
                    ],
                ],
                'finishAt' => [
                    'date' => [
                        'year' => '2020',
                        'month' => '3',
                        'day' => '3',
                    ],
                    'time' => [
                        'hour' => '19',
                        'minute' => '0',
                    ],
                ],
                'capacity' => '100',
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        // Follow the redirect and check the adherent can see the committee page
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertContains('L\'initiative citoyenne a bien été modifiée', $crawler->filter('#notice-flashes')->text());
        $this->assertSame('Nouveau titre | En Marche !', $crawler->filter('title')->text());
        $this->assertSame('Nouveau titre', $crawler->filter('.committee-event-name')->text());
        $this->assertSame('Organisé par Michel VASSEUR', trim(preg_replace('/\s+/', ' ', $crawler->filter('.committee-event-organizer')->text())));
        $this->assertSame('Mardi 3 mars 2020, 9h30', $crawler->filter('.committee-event-date')->text());
        $this->assertSame('Pilgerweg 58, 8802 Kilchberg, Suisse', $crawler->filter('.committee-event-address')->text());
        $this->assertSame('Nouvelle description.', $crawler->filter('.committee-event-description')->text());
    }

    public function testOrganizerCanCancelCitizenInitiative()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');

        $this->assertSame(1, $crawler->filter('.search__results__meta h2 a:contains("Nettoyage de la Kilchberg")')->count());

        $crawler = $this->client->request(Request::METHOD_GET, '/initiative-citoyenne/'.date('Y-m-d', strtotime('+11 days')).'-nettoyage-de-la-kilchberg/annuler');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('Oui, annuler l\'événement')->form());

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        static::assertContains('L\'initiative citoyenne a bien été annulée.', $crawler->filter('#notice-flashes')->text());

        $messages = $this->getMailjetEmailRepository()->findMessages(EventCancellationMessage::class);
        /** @var EventCancellationMessage $message */
        $message = array_shift($messages);

        // One mail have been sent
        static::assertCount(1, $message->getRecipients());

        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');

        $this->assertSame(0, $crawler->filter('.search__results__meta h2 a:contains("Nettoyage de la Kilchberg")')->count());

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/mes-evenements');

        $this->assertSame(1, $crawler->filter('.search__results__meta h2 a:contains("Nettoyage de la Kilchberg")')->count());
    }

    public function testOrganizerCanSeeRegistrations()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');
        $crawler = $this->client->request('GET', '/initiative-citoyenne/'.date('Y-m-d', strtotime('+11 days')).'-nettoyage-de-la-kilchberg');
        $crawler = $this->client->click($crawler->selectLink('Gérer les participants')->link());

        $this->assertTrue($this->seeMembersList($crawler, 2));
    }

    public function testOrganizerCanExportRegistrationsWithWrongUuids()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');

        $crawler = $this->client->request('GET', '/initiative-citoyenne/'.date('Y-m-d', strtotime('+11 days')).'-nettoyage-de-la-kilchberg');
        $crawler = $this->client->click($crawler->selectLink('Gérer les participants')->link());

        $exportUrl = $this->client->getRequest()->getPathInfo().'/exporter';

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $crawler->filter('#members-export-token')->attr('value'),
            'exports' => json_encode(['wrong_uuid']),
        ]);

        $this->assertStatusCode(Response::HTTP_BAD_REQUEST, $this->client);
    }

    public function testOrganizerCanExportRegistrations()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');
        $crawler = $this->client->request(Request::METHOD_GET, '/initiative-citoyenne/'.date('Y-m-d', strtotime('+11 days')).'-nettoyage-de-la-kilchberg');
        $crawler = $this->client->click($crawler->selectLink('Gérer les participants')->link());

        $token = $crawler->filter('#members-export-token')->attr('value');
        $uuids = (array) $crawler->filter('input[name="members[]"]')->attr('value');

        $exportUrl = $this->client->getRequest()->getPathInfo().'/exporter';

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $token,
            'exports' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(3, explode("\n", $this->client->getResponse()->getContent()));

        // Try to illegally export an adherent data
        $uuids[] = Uuid::uuid4();

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $token,
            'exports' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(3, explode("\n", $this->client->getResponse()->getContent()));

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $token,
            'exports' => json_encode([]),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }

    public function testOrganizerCanContactRegistrations()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');
        $crawler = $this->client->request(Request::METHOD_GET, '/initiative-citoyenne/'.date('Y-m-d', strtotime('+11 days')).'-nettoyage-de-la-kilchberg');
        $crawler = $this->client->click($crawler->selectLink('Gérer les participants')->link());

        $token = $crawler->filter('#members-contact-token')->attr('value');
        $uuids = (array) $crawler->filter('input[name="members[]"]')->attr('value');

        $membersUrl = $this->client->getRequest()->getPathInfo();
        $contactUrl = $membersUrl.'/contacter';

        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $token,
            'contacts' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Try to post with an empty message
        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => $crawler->filter('input[name="contacts"]')->attr('value'),
            'message' => ' ',
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('.form__errors > .form__error')->text());

        $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => $crawler->filter('input[name="contacts"]')->attr('value'),
            'message' => 'Hello!',
        ]);

        $this->assertClientIsRedirectedTo($membersUrl, $this->client);

        $crawler = $this->client->followRedirect();

        $this->seeMessageSuccesfullyCreatedFlash($crawler, 'Félicitations, votre message a bien été envoyé aux inscrits sélectionnés.');

        // Email should have been sent
        $this->assertCount(1, $this->getMailjetEmailRepository()->findMessages(EventContactMembersMessage::class));

        // Try to illegally contact an adherent
        $uuids[] = Uuid::uuid4();

        $crawler = $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $token,
            'contacts' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, json_decode($crawler->filter('input[name="contacts"]')->attr('value'), true));

        // Force the contact form with foreign uuid
        $this->client->request(Request::METHOD_POST, $contactUrl, [
            'token' => $crawler->filter('input[name="token"]')->attr('value'),
            'contacts' => json_encode($uuids),
            'message' => 'Hello!',
        ]);

        $this->assertClientIsRedirectedTo($membersUrl, $this->client);
    }

    private function seeMessageSuccesfullyCreatedFlash(Crawler $crawler, ?string $message = null)
    {
        $flash = $crawler->filter('#notice-flashes');

        if ($message) {
            $this->assertSame($message, trim($flash->text()));
        }

        return 1 === count($flash);
    }

    private function seeMembersList(Crawler $crawler, int $count): bool
    {
        // Header row is part of the count
        return $count === count($crawler->filter('table > tr'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadCitizenInitiativeCategoryData::class,
            LoadCitizenInitiativeData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}

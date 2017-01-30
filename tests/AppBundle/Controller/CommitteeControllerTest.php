<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\CommitteeEvent;
use AppBundle\Mailjet\Message\CommitteeEventNotificationMessage;
use AppBundle\Repository\CommitteeEventRepository;
use AppBundle\Repository\MailjetEmailRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CommitteeControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /* @var MailjetEmailRepository */
    private $emailRepository;

    /* @var CommitteeEventRepository */
    private $committeeEventRepository;

    public function testCommitteeFollowerIsNotAllowedToPublishNewCommitteeEvent()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        // Must be changed when there will be explicit navigation links in the page.
        $this->client->request('GET', sprintf('/comites/%s/en-marche-paris-8/evenements/ajouter', LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testCommitteeHostCanPublishNewCommitteeEvent()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        // Must be changed when there will be explicit navigation links in the page.
        $crawler = $this->client->request('GET', sprintf('/comites/%s/en-marche-paris-8/evenements/ajouter', LoadAdherentData::COMMITTEE_1_UUID));
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Submit the committee event form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('Créer cet événement')->form([
            'committee_event' => [
                'name' => 'F',
                'description' => 'F',
                'category' => 'CE003',
                'address' => [
                    'country' => 'FR',
                    'postalCode' => '99999',
                    'city' => '10102-45029',
                ],
                'beginAt' => [
                    'date' => [
                        'year' => '2017',
                        'month' => '3',
                        'day' => '2',
                    ],
                    'time' => [
                        'hour' => '14',
                        'minute' => '30',
                    ],
                ],
                'finishAt' => [
                    'date' => [
                        'year' => '2017',
                        'month' => '3',
                        'day' => '1',
                    ],
                    'time' => [
                        'hour' => '19',
                        'minute' => '0',
                    ],
                ],
                'capacity' => 'zero',
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(7, $crawler->filter('#committee-event-form .form__errors > li')->count());
        $this->assertSame('Cette chaîne est trop courte. Elle doit avoir au minimum 5 caractères.', $crawler->filter('#committee-event-name-field .form__error')->text());
        $this->assertSame('Cette chaîne est trop courte. Elle doit avoir au minimum 10 caractères.', $crawler->filter('#committee-event-description-field .form__error')->text());
        $this->assertSame('La capacité doit être un nombre entier valide.', $crawler->filter('#committee-event-capacity-field .form__error')->text());
        $this->assertSame("Cette valeur n'est pas un code postal français valide.", $crawler->filter('#committee-event-address > .form__errors > .form__error')->eq(0)->text());
        $this->assertSame("Cette adresse n'est pas géolocalisable.", $crawler->filter('#committee-event-address > .form__errors > li')->eq(1)->text());
        $this->assertSame("L'adresse est obligatoire.", $crawler->filter('#committee-event-address-address-field > .form__errors > li')->text());
        $this->assertSame("La date de fin de l'événement doit être postérieure à la date de début.", $crawler->filter('#committee-event-finishat-field > .form__errors > li')->text());

        // Submit the committee form with valid data to create the new committee event
        $this->client->submit($crawler->selectButton('Créer cet événement')->form([
            'committee_event' => [
                'name' => "Débat sur l'écologie",
                'description' => 'Cette journée sera consacrée à un grand débat sur la question écologique.',
                'category' => 'CE003',
                'address' => [
                    'address' => '6 rue Neyret',
                    'country' => 'FR',
                    'postalCode' => '69001',
                    'city' => '69001-69381',
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
        $this->assertInstanceOf(CommitteeEvent::class, $event = $this->committeeEventRepository->findMostRecentCommitteeEvent());
        $this->assertSame("Débat sur l'écologie", $event->getName());
        $this->assertCount(3, $this->emailRepository->findMessages(CommitteeEventNotificationMessage::class, (string) $event->getUuid()));
        $this->assertCount(1, $this->emailRepository->findRecipientMessages(CommitteeEventNotificationMessage::class, 'jacques.picard@en-marche.fr'));
        $this->assertCount(1, $this->emailRepository->findRecipientMessages(CommitteeEventNotificationMessage::class, 'gisele-berthoux@caramail.com'));
        $this->assertCount(1, $this->emailRepository->findRecipientMessages(CommitteeEventNotificationMessage::class, 'luciole1989@spambox.fr'));
        $this->assertCount(0, $this->emailRepository->findRecipientMessages(CommitteeEventNotificationMessage::class, 'carl999@example.fr'));

        // Follow the redirect and check the adherent can see the committee page
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertContains('Le nouvel événement a bien été créé et publié sur la page du comité.', $crawler->filter('#notice-flashes')->text());
        $this->assertSame("Débat sur l'écologie", $crawler->filter('#committee-event-name')->text());
        $this->assertSame('Cette journée sera consacrée à un grand débat sur la question écologique.', $crawler->filter('#committee-event-description')->text());
    }

    public function testShowCommitteeApprovedIsAccessibleForMembers()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $committeeUrl = sprintf('/comites/%s/%s', LoadAdherentData::COMMITTEE_3_UUID, 'en-marche-dammarie-les-lys');

        $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $this->authenticateAsAdherent($this->client, 'francis.brioul@yahoo.com', 'Champion20');

        $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testShowCommitteeNotApprovedIsAccessibleForCreator()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $committeeUrl = sprintf('/comites/%s/%s', LoadAdherentData::COMMITTEE_3_UUID, 'en-marche-dammarie-les-lys');

        $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $this->authenticateAsAdherent($this->client, 'francis.brioul@yahoo.com', 'Champion20');

        $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->client = static::createClient();
        $this->container = $this->client->getContainer();
        $this->emailRepository = $this->getMailjetEmailRepository();
        $this->committeeEventRepository = $this->getCommitteeEventRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->committeeEventRepository = null;
        $this->emailRepository = null;
        $this->container = null;
        $this->client = null;

        parent::tearDown();
    }
}

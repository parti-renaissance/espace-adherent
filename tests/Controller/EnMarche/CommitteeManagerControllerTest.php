<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\Committee\CommitteeManager;
use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedItem;
use AppBundle\Entity\CommitteeMembership;
use AppBundle\Entity\Event;
use AppBundle\Entity\PostAddress;
use AppBundle\Mailer\Message\CommitteeMessageNotificationMessage;
use AppBundle\Mailer\Message\EventNotificationMessage;
use AppBundle\Mailer\Message\EventRegistrationConfirmationMessage;
use AppBundle\Repository\CommitteeFeedItemRepository;
use AppBundle\Repository\CommitteeMembershipRepository;
use AppBundle\Repository\EventRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group committeeManager
 */
class CommitteeManagerControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /* @var EventRepository */
    private $committeeEventRepository;

    /* @var CommitteeFeedItemRepository */
    private $committeeFeedItemRepository;

    /* @var CommitteeMembershipRepository */
    private $committeeMembershipRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();

        $this->committeeEventRepository = $this->getEventRepository();
        $this->committeeFeedItemRepository = $this->getCommitteeFeedItemRepository();
        $this->committeeMembershipRepository = $this->getCommitteeMembershipRepository();
    }

    protected function tearDown(): void
    {
        $this->kill();

        $this->committeeMembershipRepository = null;
        $this->committeeFeedItemRepository = null;
        $this->committeeEventRepository = null;

        parent::tearDown();
    }

    public function testCommitteeFollowerIsNotAllowedToEditCommitteeInformation()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');
        $this->client->click($crawler->selectLink('En Marche Paris 8')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->request('GET', sprintf('%s/editer', $this->client->getRequest()->getPathInfo()));

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testCommitteeHostCanEditCommitteeInformation()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');
        $crawler = $this->client->click($crawler->selectLink('En Marche Paris 8')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Gérer le comité →')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Submit the committee form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'committee' => [
                'name' => 'F',
                'description' => 'F',
                'address' => [
                    'address' => '',
                    'country' => 'FR',
                    'postalCode' => '99999',
                    'city' => '10102-45029',
                ],
                'facebookPageUrl' => 'yo',
                'twitterNickname' => '@!!',
            ],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(6, $crawler->filter('#edit-committee-form .form__errors > li')->count());
        $this->assertSame("Votre adresse n'est pas reconnue. Vérifiez qu'elle soit correcte.", $crawler->filter('#committee-address > .form__errors > .form__error')->eq(0)->text());
        $this->assertSame("L'adresse est obligatoire.", $crawler->filter('#field-address > .form__errors > li')->text());
        $this->assertSame('Vous devez saisir au moins 2 caractères.', $crawler->filter('#field-name > .form__errors > li')->text());
        $this->assertSame('Votre texte de description est trop court. Il doit compter 5 caractères minimum.', $crawler->filter('#field-description > .form__errors > li')->text());
        $this->assertSame("Cette valeur n'est pas une URL valide.", $crawler->filter('#field-facebook-page-url > .form__errors > li')->text());
        $this->assertSame('Un identifiant Twitter ne peut contenir que des lettres, des chiffres et des underscores.', $crawler->filter('#field-twitter-nickname > .form__errors > li')->text());
        $this->assertSame("Cette valeur n'est pas une URL valide.", $crawler->filter('#field-facebook-page-url > .form__errors > li')->text());

        // Submit the committee form with valid data to create committee
        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'committee' => [
                'name' => 'clichy est En Marche !',
                'description' => 'Comité français En Marche ! de la ville de Clichy',
                'address' => [
                    'country' => 'FR',
                    'address' => '12 Rue des Saussaies',
                    'postalCode' => '92110',
                    'city' => '92110-92024',
                    'cityName' => '',
                ],
                'facebookPageUrl' => 'https://www.facebook.com/EnMarcheClichy',
                'twitterNickname' => '@enmarcheclichy',
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        // Follow the redirect and check the adherent can see the committee edit page again
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->seeFlashMessage($crawler, 'Les informations du comité ont été mises à jour avec succès.');
        $this->assertSame('Clichy est En Marche !', $crawler->filter('#committee_name')->attr('value'));
        // Address has been changed but not city and country because the committee is approved
        $this->assertSame('12 Rue des Saussaies', $crawler->filter('#committee_address_address')->attr('value'));
        $this->assertSame('75008', $crawler->filter('#committee_address_postalCode')->attr('value'));
        $this->assertSame('75008-75108', $crawler->filter('#committee_address_city')->attr('value'));
        $this->assertSame('Paris 8e', $crawler->filter('#committee_address_cityName')->attr('value'));
        $this->assertSame('France', $crawler->filter('#committee_address_country option:selected')->text());
    }

    public function testCommitteeHostCannotEditNoneditableCommitteeName()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');
        $crawler = $this->client->click($crawler->selectLink('En Marche Dammarie-les-Lys')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Gérer le comité →')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Submit the committee form with new name
        $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'committee' => [
                'name' => 'Nouveau nom',
                'description' => 'Comité français En Marche !',
                'address' => [
                    'country' => 'FR',
                    'address' => '824 Avenue du Lys',
                    'postalCode' => '92110',
                    'city' => '92110-92024',
                    'cityName' => '',
                ],
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        // Follow the redirect and check the adherent can see the committee edit page again
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        // Committee name has not been changed
        $this->assertSame('En Marche Dammarie-les-Lys', $crawler->filter('#committee_name')->attr('value'));
        $this->assertSame('Comité français En Marche !', $crawler->filter('#committee_description')->text());
    }

    public function testCommitteeHostCanEditCompletelyAddressOfPendingCommittee()
    {
        $this->client->followRedirects();

        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');
        $crawler = $this->client->click($crawler->selectLink('En Marche Marseille 3')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Submit the committee form with new address
        $crawler = $this->client->submit($crawler->selectButton('Enregistrer')->form([
            'committee' => [
                'address' => [
                    'country' => 'CH',
                    'address' => '12 Pilgerweg',
                    'postalCode' => '8802',
                    'cityName' => 'Kilchberg',
                ],
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        // Address has been changed totally
        $this->assertSame('12 Pilgerweg', $crawler->filter('#committee_address_address')->attr('value'));
        $this->assertSame('8802', $crawler->filter('#committee_address_postalCode')->attr('value'));
        $this->assertSame('Kilchberg', $crawler->filter('#committee_address_cityName')->attr('value'));
        $this->assertSame('Suisse', $crawler->filter('#committee_address_country option:selected')->text());
    }

    public function testCommitteeFollowerIsNotAllowedToPublishNewEvent()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');
        $this->client->click($crawler->selectLink('En Marche Paris 8')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->request('GET', sprintf('%s/evenements/ajouter', $this->client->getRequest()->getPathInfo()));

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testCommitteeHostCanPublishNewEvent()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');
        $crawler = $this->client->click($crawler->selectLink('En Marche Paris 8')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Gérer le comité →')->link());

        $crawler = $this->client->click($crawler->selectLink('+ Créer un événement')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $eventCategory = $this->getEventCategoryIdForName(LoadEventCategoryData::LEGACY_EVENT_CATEGORIES['CE003']);

        // Submit the committee event form with invalid data
        $crawler = $this->client->submit($crawler->selectButton('Créer cet événement')->form([
            'committee_event' => [
                'name' => 'F',
                'description' => 'F',
                'category' => $eventCategory,
                'address' => [
                    'country' => 'FR',
                    'postalCode' => '99999',
                    'city' => '10102-45029',
                ],
                'beginAt' => [
                    'date' => [
                        'year' => date('Y'),
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
                        'year' => date('Y'),
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
        $this->assertSame('Vous devez saisir au moins 5 caractères.', $crawler->filter('#committee-event-name-field .form__error')->text());
        $this->assertSame('Vous devez saisir au moins 10 caractères.', $crawler->filter('#committee-event-description-field .form__error')->text());
        $this->assertSame('Cette valeur n\'est pas valide.', $crawler->filter('#committee-event-capacity-field .form__error')->text());
        $this->assertSame("Cette valeur n'est pas un code postal français valide.", $crawler->filter('#committee-event-address > .form__errors > .form__error')->eq(0)->text());
        $this->assertSame("Votre adresse n'est pas reconnue. Vérifiez qu'elle soit correcte.", $crawler->filter('#committee-event-address > .form__errors > li')->eq(1)->text());
        $this->assertSame("L'adresse est obligatoire.", $crawler->filter('#committee-event-address-address-field > .form__errors > li')->text());
        $this->assertSame("La date de fin de l'événement doit être postérieure à la date de début.", $crawler->filter('#committee-event-finishat-field > .form__errors > li')->text());

        // Submit the committee form with valid data to create the new committee event
        $this->client->submit($crawler->selectButton('Créer cet événement')->form([
            'committee_event' => [
                'name' => " ♻ débat sur l'agriculture écologique ♻ ",
                'description' => " ♻ Cette journée sera consacrée à un grand débat sur la question de l'agriculture écologique. ♻ ",
                'category' => $eventCategory,
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
        $this->assertInstanceOf(Event::class, $event = $this->committeeEventRepository->findOneBySlug('2022-03-02-debat-sur-lagriculture-ecologique'));
        $this->assertSame("Débat sur l'agriculture écologique", $event->getName());
        $this->assertSame('Cette journée sera consacrée à un grand débat sur la question de l\'agriculture écologique.', $event->getDescription());
        $this->assertFalse($event->isForLegislatives());
        $this->assertCountMails(1, EventNotificationMessage::class, 'jacques.picard@en-marche.fr');
        $this->assertCountMails(1, EventNotificationMessage::class, 'gisele-berthoux@caramail.com');
        $this->assertCountMails(1, EventNotificationMessage::class, 'luciole1989@spambox.fr');
        $this->assertCountMails(0, EventNotificationMessage::class, 'carl999@example.fr');

        $eventItem = $this->committeeFeedItemRepository->findMostRecentFeedEvent(LoadAdherentData::COMMITTEE_1_UUID);
        $this->assertInstanceOf(CommitteeFeedItem::class, $eventItem);
        $this->assertInstanceOf(Event::class, $eventItem->getEvent());

        // Follow the redirect and check the adherent can see the committee page
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->seeFlashMessage($crawler, 'Le nouvel événement a bien été créé et publié sur la page du comité.');
        $this->assertSame('Débat sur l\'agriculture écologique - Lyon 1er, 02/03/2022 | La République En Marche !', $crawler->filter('title')->text());
        $this->assertSame('Débat sur l\'agriculture écologique - Lyon 1er, 02/03/2022', $crawler->filter('.committee-event-name')->text());
        $this->assertSame('Organisé par Gisele Berthoux du comité En Marche Paris 8', trim(preg_replace('/\s+/', ' ', $crawler->filter('.committee-event-organizer')->text())));
        $this->assertSame('Mercredi 2 mars 2022, 9h30', $crawler->filter('.committee-event-date')->text());
        $this->assertSame('6 rue Neyret, 69001 Lyon 1er', $crawler->filter('.committee-event-address')->text());
        $this->assertSame('Cette journée sera consacrée à un grand débat sur la question de l\'agriculture écologique.', $crawler->filter('.committee-event-description')->text());

        $this->assertCountMails(1, EventRegistrationConfirmationMessage::class, 'gisele-berthoux@caramail.com');
    }

    public function testCommitteeHostCanPublishNewEventWithTimeZone()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');
        $crawler = $this->client->click($crawler->selectLink('En Marche Paris 8')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->click($crawler->selectLink('Gérer le comité →')->link());
        $crawler = $this->client->click($crawler->selectLink('+ Créer un événement')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $eventCategory = $this->getEventCategoryIdForName(LoadEventCategoryData::LEGACY_EVENT_CATEGORIES['CE003']);

        $this->client->submit($crawler->selectButton('Créer cet événement')->form([
            'committee_event' => [
                'name' => " ♻ débat sur l'agriculture écologique à Singapore",
                'description' => " ♻ Cette journée sera consacrée à un grand débat sur la question de l'agriculture écologique. ♻ ",
                'category' => $eventCategory,
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
                'timeZone' => 'Asia/Singapore',
            ],
        ]));

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
        $this->assertInstanceOf(Event::class, $event = $this->committeeEventRepository->findOneBySlug('2022-03-02-debat-sur-lagriculture-ecologique-a-singapore'));
        $this->assertSame("Débat sur l'agriculture écologique à Singapore", $event->getName());
        $this->assertSame('Cette journée sera consacrée à un grand débat sur la question de l\'agriculture écologique.', $event->getDescription());
        $this->assertFalse($event->isForLegislatives());
        $this->assertCountMails(1, EventNotificationMessage::class, 'jacques.picard@en-marche.fr');
        $this->assertCountMails(1, EventNotificationMessage::class, 'gisele-berthoux@caramail.com');
        $this->assertCountMails(1, EventNotificationMessage::class, 'luciole1989@spambox.fr');
        $this->assertCountMails(0, EventNotificationMessage::class, 'carl999@example.fr');

        $eventItem = $this->committeeFeedItemRepository->findMostRecentFeedEvent(LoadAdherentData::COMMITTEE_1_UUID);
        $this->assertInstanceOf(CommitteeFeedItem::class, $eventItem);
        $this->assertInstanceOf(Event::class, $eventItem->getEvent());

        // Follow the redirect and check the adherent can see the committee page
        $crawler = $this->client->followRedirect();
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->seeFlashMessage($crawler, 'Le nouvel événement a bien été créé et publié sur la page du comité.');
        $this->assertSame('Débat sur l\'agriculture écologique à Singapore - Lyon 1er, 02/03/2022 | La République En Marche !', $crawler->filter('title')->text());
        $this->assertSame('Débat sur l\'agriculture écologique à Singapore - Lyon 1er, 02/03/2022', $crawler->filter('.committee-event-name')->text());
        $this->assertSame('Organisé par Gisele Berthoux du comité En Marche Paris 8', trim(preg_replace('/\s+/', ' ', $crawler->filter('.committee-event-organizer')->text())));
        $this->assertSame('Mercredi 2 mars 2022, 9h30 UTC +08:00', $crawler->filter('.committee-event-date')->text());
        $this->assertSame('6 rue Neyret, 69001 Lyon 1er', $crawler->filter('.committee-event-address')->text());
        $this->assertSame('Cette journée sera consacrée à un grand débat sur la question de l\'agriculture écologique.', $crawler->filter('.committee-event-description')->text());

        $this->assertCountMails(1, EventRegistrationConfirmationMessage::class, 'gisele-berthoux@caramail.com');
    }

    public function testAuthenticatedCommitteeHostCanPostMessages()
    {
        $this->markTestSkipped('Skipped temporary, need to implement this feature with a new Message form');

        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');
        $crawler = $this->client->click($crawler->selectLink('En Marche Paris 8')->link());

        $committeeUrl = $this->client->getRequest()->getPathInfo();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageForm($crawler));
        $this->assertFalse($this->seeFlashMessage($crawler));
        $this->assertCountTimelineMessages($crawler, 9);

        $crawler = $this->client->submit($crawler->selectButton('committee_feed_message[send]')->form([
            'committee_feed_message' => ['subject' => 'bonsoir', 'content' => 'yo'],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageForm($crawler, ['Le message doit contenir au moins 10 caractères.']));
        $this->assertFalse($this->seeFlashMessage($crawler));

        $this->client->submit($crawler->selectButton('committee_feed_message[send]')->form([
            'committee_feed_message' => ['subject' => 'bonsoir', 'content' => 'Bienvenue !'],
        ]));

        $this->assertClientIsRedirectedTo($committeeUrl, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageForm($crawler));
        $this->assertTrue($this->seeFlashMessage($crawler, 'Votre message a bien été envoyé.'));
        $this->assertCountTimelineMessages($crawler, 9, 'Message should not be published');

        $message = $this->committeeFeedItemRepository->findMostRecentFeedMessage(LoadAdherentData::COMMITTEE_1_UUID);
        $this->assertInstanceOf(CommitteeFeedItem::class, $message);
        $this->assertSame('Bienvenue !', $message->getContent());

        $mail = $this->getEmailRepository()->findMostRecentMessage(CommitteeMessageNotificationMessage::class);
        $this->assertMailCountRecipients(
            $this->getCommitteeSubscribersCount(
                $this->getCommittee(LoadAdherentData::COMMITTEE_1_UUID)
            ),
            $mail
        );

        $this->client->submit($crawler->selectButton('committee_feed_message[send]')->form([
            'committee_feed_message' => [
                'subject' => 'Bonsoir',
                'content' => 'Première publication !',
                'published' => '1',
            ],
        ]));

        $this->assertClientIsRedirectedTo($committeeUrl, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageForm($crawler));
        $this->assertTrue($this->seeFlashMessage($crawler, 'Votre message a bien été publié.'));
        $this->assertSeeCommitteeTimelineMessage($crawler, 0, 'Gisele Berthoux', 'co-animateur', 'Première publication !');
    }

    /**
     * @dataProvider provideFollowerCredentials
     */
    public function testAuthenticatedFollowerCannotSeeCommitteeMembers(string $username)
    {
        // Authenticate as a committee follower
        $this->authenticateAsAdherent($this->client, $username);
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');
        $this->client->click($crawler->selectLink('En Marche Paris 8')->link());
        $this->client->request(Request::METHOD_GET, sprintf('%s/membres', $this->client->getRequest()->getPathInfo()));

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function provideFollowerCredentials()
    {
        return [
            'follower 1' => ['carl999@example.fr'],
            'follower 2' => ['luciole1989@spambox.fr'],
        ];
    }

    /**
     * @dataProvider provideHostCredentials
     */
    public function testAuthenticatedHostCanSeeCommitteeMembers(string $username)
    {
        // Authenticate as the committee supervisor
        $this->authenticateAsAdherent($this->client, $username);
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');
        $crawler = $this->client->click($crawler->selectLink('En Marche Paris 8')->link());
        $crawler = $this->client->click($crawler->selectLink('Gérer le comité →')->link());
        $crawler = $this->client->click($crawler->selectLink('Adhérents')->link());

        $this->assertTrue($this->seeMembersList($crawler, 5));
        $this->assertSame('Jacques', $crawler->filter('.member-first-name')->eq(0)->text());
        $this->assertSame('P.', $crawler->filter('.member-last-name')->eq(0)->text());
        $this->assertSame('75008', $crawler->filter('.member-postal-code')->eq(0)->text());
        $this->assertSame('Paris 8e', $crawler->filter('.member-city-name')->eq(0)->text());
        $this->assertSame('12/01/2017', $crawler->filter('.member-subscription-date')->eq(0)->text());
    }

    public function provideHostCredentials()
    {
        return [
            'supervisor' => ['jacques.picard@en-marche.fr', 'changeme1337'],
            'host' => ['gisele-berthoux@caramail.com', 'ILoveYouManu'],
        ];
    }

    public function testAuthenticatedCommitteeSupervisorCanPromoteNewHostsAmongMembers()
    {
        // Authenticate as the committee supervisor
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');
        $crawler = $this->client->click($crawler->selectLink('En Marche Paris 8')->link());
        $crawler = $this->client->click($crawler->selectLink('Gérer le comité →')->link());
        $crawler = $this->client->click($crawler->selectLink('Adhérents')->link());

        $this->assertSame(2, $crawler->filter('.promote-host-link')->count());
        $crawler = $this->client->click($crawler->filter('.promote-host-link')->link());

        $this->client->submit($crawler->selectButton("Oui, promouvoir l'adhérent")->form());
        $crawler = $this->client->followRedirect();

        $this->assertSame(1, $crawler->filter('.promote-host-link')->count());
        $this->seeFlashMessage($crawler, 'Le membre a été promu animateur du comité avec succès.');
    }

    public function testAuthenticatedCommitteeHostCannotPromoteNewHostsAmongMembers()
    {
        // Authenticate as the committee supervisor
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');
        $crawler = $this->client->click($crawler->selectLink('En Marche Paris 8')->link());
        $crawler = $this->client->click($crawler->selectLink('Gérer le comité →')->link());
        $crawler = $this->client->click($crawler->selectLink('Adhérents')->link());

        $this->assertSame(0, $crawler->filter('.promote-host-link')->count());
    }

    public function testCommitteeExportMembers()
    {
        // Authenticate as the committee supervisor
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');
        $crawler = $this->client->click($crawler->selectLink('En Marche Paris 8')->link());
        $crawler = $this->client->click($crawler->selectLink('Gérer le comité →')->link());
        $crawler = $this->client->click($crawler->selectLink('Adhérents')->link());

        $token = $crawler->filter('#members-export-token')->attr('value');
        $uuids = (array) $crawler->filter('input[name="members[]"]')->attr('value');

        $exportUrl = $this->client->getRequest()->getPathInfo().'/export';

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $token,
            'exports' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $lines = $this->transformToArray($this->client->getResponse()->getContent());
        $this->assertCount(2, $lines);

        // Try to illegally export an adherent data
        $uuids[] = LoadAdherentData::ADHERENT_1_UUID;

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $token,
            'exports' => json_encode($uuids),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $lines = $this->transformToArray($this->client->getResponse()->getContent());
        $this->assertCount(2, $lines);

        $this->client->request(Request::METHOD_POST, $exportUrl, [
            'token' => $token,
            'exports' => json_encode([]),
        ]);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $lines = $this->transformToArray($this->client->getResponse()->getContent());
        $this->assertCount(1, $lines);
    }

    public function testAllowToCreateCommmitee()
    {
        /** @var CommitteeManager $manager */
        $manager = $this->get('app.committee.manager');

        $this->authenticateAsAdherent($this->client, 'martine.lindt@gmail.com');
        $adherent = $this->getAdherentRepository()->findOneByEmail('martine.lindt@gmail.com');
        $committee = $this->getCommitteeRepository()->findOneByName('En Marche - Comité de Berlin');

        $this->client->request('GET', '/espace-adherent/creer-mon-comite');

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
        $manager->changePrivilege(
            $adherent,
            $committee,
            CommitteeMembership::COMMITTEE_FOLLOWER
        );
        $this->client->request('GET', '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $manager->changePrivilege(
            $adherent,
            $committee,
            CommitteeMembership::COMMITTEE_HOST
        );

        $this->client->request('GET', '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $this->logout($this->client);

        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch');

        $adherent = $this->getAdherentRepository()->findOneByEmail('michel.vasseur@example.ch');
        $manager->unfollowCommittee($adherent, $this->getCommitteeRepository()->findOneByName('En Marche - Suisse'));

        $committee = $this->get('app.committee.factory')->createFromArray([
            'uuid' => '79638242-6662-11e7-b114-ef08860a1845',
            'created_by' => (string) $adherent->getUuid(),
            'created_at' => '2017-01-12 19:34:12',
            'name' => 'En Marche Lille 20',
            'description' => "En Marche ! C'est aussi dans le NORD",
            'address' => PostAddress::createFrenchAddress('30 Boulevard Louis Guichoux', '13003-13203', 43.3256095, 5.374416),
            'phone' => '33 673643424',
        ]);
        $this->manager->persist($committee);
        $this->manager->flush();
        $committee = $this->getCommitteeRepository()->findOneByName('En Marche Lille 20');

        $this->client->request('GET', '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $manager->preApproveCommittee($committee);
        $this->client->request('GET', '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $manager->preRefuseCommittee($committee);
        $this->client->request('GET', '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $manager->refuseCommittee($committee);
        $this->client->request('GET', '/espace-adherent/creer-mon-comite');
        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $this->logout($this->client);

        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $this->client->request('GET', '/espace-adherent/creer-mon-comite');

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    private function getCommitteeSubscribersCount(Committee $committee): int
    {
        return $this
            ->committeeMembershipRepository
            ->findFollowers($committee)
            ->getCommitteesNotificationsSubscribers()
            ->count()
        ;
    }

    private function seeMembersList(Crawler $crawler, int $count): bool
    {
        // Header row is part of the count
        return $count === \count($crawler->filter('table tr'));
    }

    private function seeMessageForm(Crawler $crawler, array $errorMessages = []): bool
    {
        if ($errorMessages) {
            $errors = $crawler->filter('form[name="committee_feed_message"] .form__error');

            $this->assertCount(\count($errorMessages), $errors);

            foreach ($errorMessages as $i => $errorMessage) {
                $this->assertSame($errorMessage, trim($errors->eq($i)->text()));
            }
        } else {
            $this->assertCount(0, $crawler->filter('form[name="committee_feed_message"] .form__errors'));
        }

        return 1 === \count($crawler->filter('form[name="committee_feed_message"]'));
    }

    private function assertCountTimelineMessages(Crawler $crawler, int $nb, string $message = '')
    {
        $this->assertSame($nb, $crawler->filter('.committee__timeline__message')->count(), $message);
    }

    private function transformToArray(string $encodedData): array
    {
        $tmpHandle = \tmpfile();
        fwrite($tmpHandle, $encodedData);
        $metaDatas = stream_get_meta_data($tmpHandle);
        $tmpFilename = $metaDatas['uri'];

        $reader = new Xlsx();
        $spreadsheet = $reader->load($tmpFilename);
        $array = $spreadsheet->getActiveSheet()->toArray();

        return $array;
    }
}

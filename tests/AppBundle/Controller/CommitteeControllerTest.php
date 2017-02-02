<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\Entity\CommitteeEvent;
use AppBundle\Mailjet\Message\CommitteeEventNotificationMessage;
use AppBundle\Repository\CommitteeEventRepository;
use AppBundle\Repository\MailjetEmailRepository;
use AppBundle\Entity\Committee;
use AppBundle\Entity\CommitteeFeedMessage;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
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

    public function testShowCommitteeForGuest()
    {
        $committeeUrl = sprintf('/comites/%s/%s', LoadAdherentData::COMMITTEE_1_UUID, 'en-marche-paris-8');

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeRegisterLink($crawler), 'The guest should see the "register link"');
        $this->assertFalse($this->seeFollowLink($crawler), 'The guest should not see the "follow link"');
        $this->assertFalse($this->seeUnfollowLink($crawler), 'The guest should not see the "unfollow link"');
        $this->assertTrue($this->seeMembersCount($crawler, 4), 'The guest should see the members count');
        $this->assertTrue($this->seeHosts($crawler, 2), 'The guest should see the hosts');
        $this->assertTrue($this->seeHostsContactLink($crawler, 2), 'The guest should see the hosts contact link');
        $this->assertFalse($this->seeHostNav($crawler), 'The guest should not see the host navigation');
        $this->assertSeeSocialLinks(
            $crawler,
            $this->getCommitteeRepository()->findOneByUuid(LoadAdherentData::COMMITTEE_1_UUID)
        );
        $this->assertFalse($this->seeMessageForm($crawler));
    }

    public function testShowCommitteeForAdherent()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');

        $committeeUrl = sprintf('/comites/%s/%s', LoadAdherentData::COMMITTEE_1_UUID, 'en-marche-paris-8');

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertFalse($this->seeRegisterLink($crawler, 0), 'The adherent should not see the "register link"');
        $this->assertTrue($this->seeFollowLink($crawler), 'The adherent should see the "follow link"');
        $this->assertFalse($this->seeUnfollowLink($crawler), 'The adherent should not see the "unfollow link"');
        $this->assertTrue($this->seeMembersCount($crawler, 4), 'The adherent should see the members count');
        $this->assertTrue($this->seeHosts($crawler, 2), 'The adherent should see the hosts');
        $this->assertTrue($this->seeHostsContactLink($crawler, 2), 'The adherent should see the hosts contact link');
        $this->assertFalse($this->seeHostNav($crawler), 'The adherent should not see the host navigation');
        $this->assertFalse($this->seeMessageForm($crawler));
    }

    public function testShowCommitteeForFollower()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $committeeUrl = sprintf('/comites/%s/%s', LoadAdherentData::COMMITTEE_1_UUID, 'en-marche-paris-8');

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertFalse($this->seeRegisterLink($crawler, 0), 'The follower should not see the "register link"');
        $this->assertFalse($this->seeFollowLink($crawler), 'The follower should not see the "follow link"');
        $this->assertTrue($this->seeUnfollowLink($crawler), 'The follower should see the "unfollow link"');
        $this->assertTrue($this->seeMembersCount($crawler, 4), 'The follower should see the members count');
        $this->assertTrue($this->seeHosts($crawler, 2), 'The follower should see the hosts');
        $this->assertTrue($this->seeHostsContactLink($crawler, 2), 'The follower should see the hosts contact link');
        $this->assertFalse($this->seeHostNav($crawler), 'The follower should not see the host navigation');
        $this->assertFalse($this->seeMessageForm($crawler));
    }

    public function testShowCommitteeForHost()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $committeeUrl = sprintf('/comites/%s/%s', LoadAdherentData::COMMITTEE_1_UUID, 'en-marche-paris-8');

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertFalse($this->seeRegisterLink($crawler, 0), 'The host should not see the "register link"');
        $this->assertFalse($this->seeFollowLink($crawler), 'The host should not see the "follow link"');
        $this->assertTrue($this->seeUnfollowLink($crawler), 'The host should see the "unfollow link" because there is another host');
        $this->assertTrue($this->seeMembersCount($crawler, 4), 'The host should see the members count');
        $this->assertTrue($this->seeHosts($crawler, 2), 'The host should see the hosts');
        $this->assertTrue($this->seeHostsContactLink($crawler, 1), 'The host should see the other contact links');
        $this->assertTrue($this->seeSelfHostContactLink($crawler, 'Gisele Berthoux'), 'The host should see his own contact link');
        $this->assertTrue($this->seeHostNav($crawler), 'The host should see the host navigation');
        $this->assertTrue($this->seeMessageForm($crawler));
    }

    public function testHostPostCommitteeMessage()
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com', 'ILoveYouManu');

        $committeeUrl = sprintf('/comites/%s/%s', LoadAdherentData::COMMITTEE_1_UUID, 'en-marche-paris-8');

        $crawler = $this->client->request(Request::METHOD_GET, $committeeUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageForm($crawler));
        $this->assertFalse($this->seeMessageSuccesfullyCreatedFlash($crawler));

        $crawler = $this->client->submit($crawler->selectButton('committee_feed_message[publish]')->form([
            'committee_feed_message' => ['content' => 'yo'],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageForm($crawler, ['Le message doit contenir au moins 10 caractères.']));
        $this->assertFalse($this->seeMessageSuccesfullyCreatedFlash($crawler));

        $crawler = $this->client->submit($crawler->selectButton('committee_feed_message[publish]')->form([
            'committee_feed_message' => ['content' => str_repeat('h', 1501)],
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageForm($crawler, ['Le message doit contenir moins de 1500 caractères.']));
        $this->assertFalse($this->seeMessageSuccesfullyCreatedFlash($crawler));

        $this->client->submit($crawler->selectButton('committee_feed_message[publish]')->form([
            'committee_feed_message' => ['content' => 'Bienvenue !'],
        ]));

        $this->assertClientIsRedirectedTo($committeeUrl, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageForm($crawler));
        $this->assertTrue($this->seeMessageSuccesfullyCreatedFlash($crawler, 'Votre message a bien été publié.'));

        $adherent = $this->getAdherentRepository()->findByEmail('gisele-berthoux@caramail.com');
        $message = $this->getCommitteeFeedMessageRepository()->findOneByAuthor($adherent);

        $this->assertInstanceOf(CommitteeFeedMessage::class, $message);
        $this->assertSame('Bienvenue !', $message->getContent());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([
            LoadAdherentData::class,
        ]);

        $this->container = $this->getContainer();
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
        $this->container = null;
        $this->client = null;

        parent::tearDown();
    }

    private function seeRegisterLink(Crawler $crawler, $do = 1): bool
    {
        $this->assertCount($do, $crawler->filter('.committee-follow-disabled'));

        return 1 === count($crawler->filter('a#committee-register-link'));
    }

    private function seeFollowLink(Crawler $crawler): bool
    {
        return 1 === count($crawler->filter('a.committee-link.committee-follow'));
    }

    private function seeUnfollowLink(Crawler $crawler): bool
    {
        return 1 === count($crawler->filter('a.committee-link.committee-unfollow'));
    }

    private function seeMembersCount(Crawler $crawler, string $membersCount): bool
    {
        return $membersCount.' membre'.($membersCount > 1 ? 's' : '') === $crawler->filter('.committee-details h4')->text();
    }

    private function seeHosts(Crawler $crawler, int $hostsCount): bool
    {
        return $hostsCount === count($crawler->filter('.committee-details .committee-host'));
    }

    private function seeHostsContactLink(Crawler $crawler, int $hostsCount): bool
    {
        return $hostsCount === count($crawler->filter('.committee-details .committee-host a'));
    }

    private function seeSelfHostContactLink(Crawler $crawler, string $name): bool
    {
        /** @var \DOMElement $host */
        foreach ($crawler->filter('.committee-details .committee-host') as $host) {
            if (false !== strpos($host->textContent, 'Contacter')) {
                continue;
            }

            return preg_match('/'.preg_quote($name).'\s+\(vous\)/', $host->textContent);
        }

        return false;
    }

    private function seeHostNav(Crawler $crawler): bool
    {
        return 1 === count($crawler->filter('#committee-host-nav'));
    }

    private function seeMessageForm(Crawler $crawler, array $errorMessages = []): bool
    {
        if ($errorMessages) {
            $errors = $crawler->filter('form[name="committee_feed_message"] .form__error');

            $this->assertCount(count($errorMessages), $errors);

            foreach ($errorMessages as $i => $errorMessage) {
                $this->assertSame($errorMessage, trim($errors->eq($i)->text()));
            }
        } else {
            $this->assertCount(0, $crawler->filter('form[name="committee_feed_message"] .form__errors'));
        }

        return 1 === count($crawler->filter('form[name="committee_feed_message"]'));
    }

    private function seeMessageSuccesfullyCreatedFlash(Crawler $crawler, ?string $message = null)
    {
        $flash = $crawler->filter('#notice-flashes');

        if ($message) {
            $this->assertSame($message, trim($flash->text()));
        }

        return 1 === count($flash);
    }

    private function assertSeeSocialLinks(Crawler $crawler, Committee $committee)
    {
        $facebookLinkPattern = 'a.committee-facebook';
        $googlePlusLinkPattern = 'a.committee-google_plus';
        $twitterLinkPattern = 'a.committee-twitter';

        if ($facebookUrl = $committee->getFacebookPageUrl()) {
            $this->assertCount(1, $facebookLink = $crawler->filter($facebookLinkPattern));
            $this->assertSame($facebookUrl, $facebookLink->attr('href'));
        } else {
            $this->assertCount(0, $crawler->filter($facebookLinkPattern));
        }

        if ($googlePlusUrl = $committee->getGooglePlusPageUrl()) {
            $this->assertCount(1, $googlePlusLink = $crawler->filter($googlePlusLinkPattern));
            $this->assertSame($googlePlusUrl, $googlePlusLink->attr('href'));
        } else {
            $this->assertCount(0, $crawler->filter($googlePlusLinkPattern));
        }

        if ($twitterNickname = $committee->getTwitterNickname()) {
            $this->assertCount(1, $twitterLink = $crawler->filter($twitterLinkPattern));
            $this->assertSame(sprintf('https://twitter.com/%s', $twitterNickname), $twitterLink->attr('href'));
        } else {
            $this->assertCount(0, $crawler->filter($twitterLinkPattern));
        }
    }
}

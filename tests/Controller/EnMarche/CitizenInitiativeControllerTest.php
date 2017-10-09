<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenInitiativeCategoryData;
use AppBundle\DataFixtures\ORM\LoadCitizenInitiativeData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\EventInvite;
use AppBundle\Entity\EventRegistration;
use AppBundle\Mailjet\Message\CitizenInitiativeCreationConfirmationMessage;
use AppBundle\Mailjet\Message\CitizenInitiativeInvitationMessage;
use AppBundle\Mailjet\Message\CitizenInitiativeRegistrationConfirmationMessage;
use AppBundle\Mailjet\Message\CommitteeCitizenInitiativeNotificationMessage;
use AppBundle\Mailjet\Message\CommitteeCitizenInitiativeOrganizerNotificationMessage;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 * @group citizenInitiative
 */
class CitizenInitiativeControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    private $repository;

    public function testAnonymousUserCannotCreateCitizenInitiative()
    {
        // Anonymous
        $this->client->request(Request::METHOD_GET, '/initiative-citoyenne/creer');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/campus', $this->client);
    }

    public function testAnonymousUserSeePartialName()
    {
        // Anonymous
        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');

        $this->assertSame(2, $crawler->filter('.search__results__meta span:contains("Jacques P.")')->count());
        $this->assertSame(0, $crawler->filter('.search__results__meta span:contains("Jacques Picard")')->count());

        $crawler = $this->client->request(Request::METHOD_GET, '/initiative-citoyenne/'.date('Y-m-d', strtotime('tomorrow')).'-apprenez-a-sauver-des-vies');

        $this->assertSame('Organisé par Jacques P.', trim(preg_replace('/\s+/', ' ', $crawler->filter('.committee-event-organizer')->text())));
    }

    public function testAdherentSeeFullName()
    {
        // Adherent
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr', 'EnMarche2017');

        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');

        $this->assertSame(0, $crawler->filter('.search__results__meta span:contains("Jacques P.")')->count());
        $this->assertSame(2, $crawler->filter('.search__results__meta span:contains("Jacques Picard")')->count());

        $crawler = $this->client->request(Request::METHOD_GET, '/initiative-citoyenne/'.date('Y-m-d', strtotime('tomorrow')).'-apprenez-a-sauver-des-vies');

        $this->assertSame('Organisé par Jacques Picard', trim(preg_replace('/\s+/', ' ', $crawler->filter('.committee-event-organizer')->text())));
    }

    public function testHostCanCreateCitizenInitiative()
    {
        // Login as supervisor
        $crawler = $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(2, $crawler->filter('a:contains("Créer une initiative")')->count());

        $this->client->request(Request::METHOD_GET, '/initiative-citoyenne/creer');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
    }

    public function testAdherentCreateCitizenInitiative()
    {
        // Login as Adherent not AL
        $crawler = $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(2, $crawler->filter('a:contains("Créer une initiative")')->count());

        $this->client->click($crawler->selectLink('Créer une initiative')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Je crée mon initiative citoyenne', $this->client->getResponse()->getContent());
    }

    public function testCreateCitizenInitiativeFailed()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');
        $this->client->request(Request::METHOD_GET, '/initiative-citoyenne/creer');

        $data = [];
        $this->client->submit($this->client->getCrawler()->selectButton('Je crée mon initiative')->form(), $data);

        $this->assertSame(4, $this->client->getCrawler()->filter('.form__errors')->count());
        $this->assertSame(
            'Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#citizen-initiative-name-field > .form__errors > li')->text()
        );
        $this->assertSame(
            'Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#citizen-initiative-description-field > .form__errors > li')->text()
        );
        $this->assertSame(
            'L\'adresse est obligatoire.',
            $this->client->getCrawler()->filter('#citizen-initiative-address-address-field > .form__errors > li')->text()
        );
        $this->assertSame(
            'Votre adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.',
            $this->client->getCrawler()->filter('#citizen-initiative-address > .form__errors > li')->text()
        );
    }

    public function testCreateCitizenInitiativeSuccessful()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, '/evenements');

        $this->assertSame(0, $crawler->filter('.search__results__meta h2 a:contains("Mon initiative")')->count());

        $this->client->request(Request::METHOD_GET, '/initiative-citoyenne/creer');

        $data = [];
        $data['citizen_initiative']['name'] = 'Mon initiative';
        $data['citizen_initiative']['beginAt']['date']['day'] = 14;
        $data['citizen_initiative']['beginAt']['date']['month'] = 12;
        $data['citizen_initiative']['beginAt']['date']['year'] = 2017;
        $data['citizen_initiative']['beginAt']['time']['hour'] = 9;
        $data['citizen_initiative']['beginAt']['time']['minute'] = 0;
        $data['citizen_initiative']['finishAt']['date']['day'] = 15;
        $data['citizen_initiative']['finishAt']['date']['month'] = 12;
        $data['citizen_initiative']['finishAt']['date']['year'] = 2017;
        $data['citizen_initiative']['finishAt']['time']['hour'] = 18;
        $data['citizen_initiative']['finishAt']['time']['minute'] = 0;
        $data['citizen_initiative']['place'] = 'Fablab';
        $data['citizen_initiative']['address']['address'] = 'Pilgerweg 58';
        $data['citizen_initiative']['address']['cityName'] = 'Kilchberg';
        $data['citizen_initiative']['address']['postalCode'] = '8802';
        $data['citizen_initiative']['address']['country'] = 'CH';
        $data['citizen_initiative']['description'] = 'Mon initiative en Suisse';
        $data['citizen_initiative']['expert_assistance_needed'] = 1;
        $data['citizen_initiative']['coaching_requested'] = 1;
        $data['citizen_initiative']['coaching_request']['problem_description'] = 'Mon problème est ...';
        $data['citizen_initiative']['coaching_request']['proposed_solution'] = 'Voici ma proposition';
        $data['citizen_initiative']['coaching_request']['required_means'] = "Voilà ce dont j'ai besoin";
        $data['citizen_initiative']['interests'][] = 'agriculture';

        $this->client->submit($this->client->getCrawler()->selectButton('Je crée mon initiative')->form(), $data);

        $initiative = $this->getCitizenInitiativeRepository()->findOneBy(['name' => 'Mon initiative']);

        $this->assertCount(1, $this->getMailjetEmailRepository()->findRecipientMessages(CitizenInitiativeCreationConfirmationMessage::class, 'michel.vasseur@example.ch'));
        $this->assertInstanceOf(CitizenInitiative::class, $initiative);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/initiative-citoyenne/creer', $this->client);
    }

    public function testShowUnpublishedInitiative()
    {
        $eventUrl = '/initiative-citoyenne/'.date('Y-m-d', strtotime('+15 days')).'-nettoyage-de-la-kilchberg-non-publiee';
        $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_MOVED_PERMANENTLY, $this->client->getResponse());
    }

    public function testShowPublishedInitiative()
    {
        $eventUrl = '/initiative-citoyenne/'.date('Y-m-d', strtotime('+11 days')).'-nettoyage-de-la-ville';
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('1 / 20 inscrits', trim($crawler->filter('.committee-event-attendees')->text()));
    }

    public function testInviteToUnpublishedEvent()
    {
        $initiative = $this->getCitizenInitiativeRepository()->findOneByUuid(LoadCitizenInitiativeData::CITIZEN_INITIATIVE_8_UUID);
        $initiativeUrl = sprintf('/initiative-citoyenne/%s', $slug = $initiative->getSlug());
        $this->client->request(Request::METHOD_GET, $initiativeUrl.'/invitation');

        $this->assertResponseStatusCode(Response::HTTP_MOVED_PERMANENTLY, $this->client->getResponse());
    }

    public function testAdherentCanInviteToEvent()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');
        $initiative = $this->getCitizenInitiativeRepository()->findOneByUuid(LoadCitizenInitiativeData::CITIZEN_INITIATIVE_4_UUID);
        $initiativeUrl = sprintf('/initiative-citoyenne/%s', $slug = $initiative->getSlug());

        $this->assertCount(0, $this->manager->getRepository(EventInvite::class)->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, $initiativeUrl.'/invitation');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=event_invitation]')->form([
            'event_invitation[message]' => 'Venez mes amis !',
            'event_invitation[guests][0]' => 'hugo.hamon@clichy-beach.com',
            'event_invitation[guests][1]' => 'jules.pietri@clichy-beach.com',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo($initiativeUrl.'/invitation/merci', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertContains('Merci ! Vos 2 invitations ont bien été envoyées !', trim($crawler->filter('.event_invitation-result > p')->text()));

        // Invitation should have been saved
        $this->assertCount(1, $invitations = $this->manager->getRepository(EventInvite::class)->findAll());

        /** @var EventInvite $invite */
        $invite = $invitations[0];

        $this->assertSame('jacques.picard@en-marche.fr', $invite->getEmail());
        $this->assertSame('Jacques Picard', $invite->getFullName());
        $this->assertSame('hugo.hamon@clichy-beach.com', $invite->getGuests()[0]);
        $this->assertSame('jules.pietri@clichy-beach.com', $invite->getGuests()[1]);

        // Email should have been sent
        $this->assertCount(1, $messages = $this->getMailjetEmailRepository()->findMessages(CitizenInitiativeInvitationMessage::class));
        $this->assertContains(str_replace('/', '\/', $initiativeUrl), $messages[0]->getRequestPayloadJson());
    }

    public function testAnonymousCanInviteToEvent()
    {
        $initiative = $this->getCitizenInitiativeRepository()->findOneByUuid(LoadCitizenInitiativeData::CITIZEN_INITIATIVE_4_UUID);
        $initiativeUrl = sprintf('/initiative-citoyenne/%s', $slug = $initiative->getSlug());

        $this->assertCount(0, $this->manager->getRepository(EventInvite::class)->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, $initiativeUrl.'/invitation');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=event_invitation]')->form([
            'event_invitation[email]' => 'damien@test-en-marche.fr',
            'event_invitation[firstName]' => 'Damien',
            'event_invitation[lastName]' => 'BRETON',
            'event_invitation[message]' => 'Venez mes amis !',
            'event_invitation[guests][0]' => 'hugo.hamon@clichy-beach.com',
            'event_invitation[guests][1]' => 'jules.pietri@clichy-beach.com',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo($initiativeUrl.'/invitation/merci', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertContains('Merci ! Vos 2 invitations ont bien été envoyées !', trim($crawler->filter('.event_invitation-result > p')->text()));

        // Invitation should have been saved
        $this->assertCount(1, $invitations = $this->manager->getRepository(EventInvite::class)->findAll());

        /** @var EventInvite $invite */
        $invite = $invitations[0];

        $this->assertSame('damien@test-en-marche.fr', $invite->getEmail());
        $this->assertSame('Damien BRETON', $invite->getFullName());
        $this->assertSame('hugo.hamon@clichy-beach.com', $invite->getGuests()[0]);
        $this->assertSame('jules.pietri@clichy-beach.com', $invite->getGuests()[1]);

        // Email should have been sent
        $this->assertCount(1, $messages = $this->getMailjetEmailRepository()->findMessages(CitizenInitiativeInvitationMessage::class));
        $this->assertContains(str_replace('/', '\/', $initiativeUrl), $messages[0]->getRequestPayloadJson());
    }

    public function testInvitationSentWithoutRedirection()
    {
        $initiative = $this->getCitizenInitiativeRepository()->findOneByUuid(LoadCitizenInitiativeData::CITIZEN_INITIATIVE_3_UUID);

        $this->client->request(Request::METHOD_GET, sprintf('/initiative-citoyenne/%s/invitation/merci', $initiative->getSlug()));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }

    public function testRegisterToUnpublishedEvent()
    {
        $initiative = $this->getCitizenInitiativeRepository()->findOneByUuid(LoadCitizenInitiativeData::CITIZEN_INITIATIVE_8_UUID);
        $initiativeUrl = sprintf('/initiative-citoyenne/%s', $slug = $initiative->getSlug());
        $this->client->request(Request::METHOD_GET, $initiativeUrl.'/inscription');

        $this->assertResponseStatusCode(Response::HTTP_MOVED_PERMANENTLY, $this->client->getResponse());
    }

    public function testAnonymousUserCanRegisterToEvent()
    {
        $eventUrl = '/initiative-citoyenne/'.date('Y-m-d', strtotime('tomorrow')).'-apprenez-a-sauver-des-vies';
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('1 / 20 inscrits', trim($crawler->filter('.committee-event-attendees')->text()));

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

        $this->assertInstanceOf(EventRegistration::class, $this->repository->findGuestRegistration(LoadCitizenInitiativeData::CITIZEN_INITIATIVE_3_UUID, 'paupau75@example.org'));
        $this->assertCount(1, $this->getMailjetEmailRepository()->findRecipientMessages(CitizenInitiativeRegistrationConfirmationMessage::class, 'paupau75@example.org'));

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageSuccesfullyCreatedFlash($crawler, 'Votre inscription est confirmée.'));
        $this->assertContains('Votre participation est bien enregistrée !', $crawler->filter('.committee-event-registration-confirmation p')->text());

        $crawler = $this->client->click($crawler->selectLink('Retour')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('2 / 20 inscrits', trim($crawler->filter('.committee-event-attendees')->text()));
    }

    public function testRegisteredAdherentUserCanRegisterToEvent()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');

        $eventUrl = '/initiative-citoyenne/'.date('Y-m-d', strtotime('+11 days')).'-nettoyage-de-la-ville';
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('1 / 20 inscrits', trim($crawler->filter('.committee-event-attendees')->text()));

        $crawler = $this->client->click($crawler->selectLink('Je veux participer')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('Benjamin', $crawler->filter('#field-first-name > input[type="text"]')->attr('value'));
        $this->assertSame('13003', $crawler->filter('#field-postal-code > input[type="text"]')->attr('value'));
        $this->assertSame('benjyd@aol.com', $crawler->filter('#field-email-address > input[type="email"]')->attr('value'));

        $this->client->submit($crawler->selectButton("Je m'inscris")->form());

        $this->assertInstanceOf(EventRegistration::class, $this->repository->findGuestRegistration(LoadCitizenInitiativeData::CITIZEN_INITIATIVE_4_UUID, 'benjyd@aol.com'));
        $this->assertCount(1, $this->getMailjetEmailRepository()->findRecipientMessages(CitizenInitiativeRegistrationConfirmationMessage::class, 'benjyd@aol.com'));

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageSuccesfullyCreatedFlash($crawler, 'Votre inscription est confirmée.'));
        $this->assertContains('Votre participation est bien enregistrée !', $crawler->filter('.committee-event-registration-confirmation p')->text());

        $crawler = $this->client->click($crawler->selectLink('Retour')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame('2 / 20 inscrits', trim($crawler->filter('.committee-event-attendees')->text()));

        $this->client->click($crawler->selectLink('Mes événements')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Nettoyage de la ville', $this->client->getResponse()->getContent());
    }

    public function testCantRegisterToAFullEvent()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');

        $eventUrl = '/initiative-citoyenne/'.date('Y-m-d', strtotime('+15 days')).'-nettoyage-de-la-ville-kilchberg';
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $headerText = $crawler->filter('.committee__event__header__cta')->text();
        $this->assertContains('10 / 10 inscrit', $headerText);
        $this->assertNotContains('JE VEUX PARTICIPER', $headerText);

        $crawler = $this->client->request('GET', $eventUrl.'/inscription');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertSame('Benjamin', $crawler->filter('#field-first-name > input[type="text"]')->attr('value'));
        $this->assertSame('13003', $crawler->filter('#field-postal-code > input[type="text"]')->attr('value'));
        $this->assertSame('benjyd@aol.com', $crawler->filter('#field-email-address > input[type="email"]')->attr('value'));

        $crawler = $this->client->submit($crawler->selectButton("Je m'inscris")->form());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains("L'événement est complet.", $crawler->filter('.form__errors')->text());
    }

    public function testShareToCommitteeToUnpublishedEvent()
    {
        $initiative = $this->getCitizenInitiativeRepository()->findOneByUuid(LoadCitizenInitiativeData::CITIZEN_INITIATIVE_8_UUID);
        $initiativeUrl = sprintf('/initiative-citoyenne/%s', $slug = $initiative->getSlug());
        $this->client->request(Request::METHOD_GET, $initiativeUrl.'/partage-au-comite');

        $this->assertResponseStatusCode(Response::HTTP_MOVED_PERMANENTLY, $this->client->getResponse());
    }

    public function testAnonymousUserShareToCommittee()
    {
        $eventUrl = '/initiative-citoyenne/'.date('Y-m-d', strtotime('+11 days')).'-nettoyage-de-la-ville';
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertNotContains('Partager dans mon comité', $crawler->filter('.committee__event__header__cta')->text());
    }

    public function testNoSupervisorAdherentShareToCommittee()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $eventUrl = '/initiative-citoyenne/'.date('Y-m-d', strtotime('+11 days')).'-nettoyage-de-la-ville';
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertNotContains('Partager dans mon comité', $crawler->filter('.committee__event__header__cta')->text());
    }

    public function testSupervisorAdherentShareToCommitteeWithPublishedFalse()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');

        $eventUrl = '/initiative-citoyenne/'.date('Y-m-d', strtotime('+11 days')).'-nettoyage-de-la-ville';
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Partager dans mon comité', $crawler->filter('.committee__event__header__cta')->text());

        $crawler = $this->client->click($crawler->selectLink('Partager dans mon comité')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertEmpty($crawler->filter('#committee_feed_citizen_initiative_message_content')->attr('value'));

        $crawler = $this->client->submit($crawler->selectButton('Envoyer')->form());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('.form__errors')->count());
        $this->assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('#committee_feed_citizen_initiative_message .form__errors > li')->text());

        $this->client->submit($crawler->selectButton('Envoyer')->form([
            'committee_feed_citizen_initiative_message' => [
                'content' => 'Cette initiative est vraiment une excellente idée',
                'published' => false,
            ],
        ]));

        $this->assertCount(1, $this->getMailjetEmailRepository()->findRecipientMessages(CommitteeCitizenInitiativeOrganizerNotificationMessage::class, 'jacques.picard@en-marche.fr'));
        $this->assertCount(1, $this->getMailjetEmailRepository()->findRecipientMessages(CommitteeCitizenInitiativeNotificationMessage::class, 'luciole1989@spambox.fr'));

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageSuccesfullyCreatedFlash($crawler, 'Votre message a bien été envoyé.'));
        $this->assertContains('En Marche Paris 8', $crawler->filter('#committee-name')->text());
        $this->assertNotContains('Cette initiative est vraiment une excellente idée', $crawler->filter('#committee-timeline')->text());
    }

    public function testSupervisorAdherentShareToCommitteeWithPublishedTrue()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');

        $eventUrl = '/initiative-citoyenne/'.date('Y-m-d', strtotime('+11 days')).'-nettoyage-de-la-ville';
        $crawler = $this->client->request('GET', $eventUrl);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Partager dans mon comité', $crawler->filter('.committee__event__header__cta')->text());

        $crawler = $this->client->click($crawler->selectLink('Partager dans mon comité')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertEmpty($crawler->filter('#committee_feed_citizen_initiative_message_content')->attr('value'));

        $crawler = $this->client->submit($crawler->selectButton('Envoyer')->form());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(1, $crawler->filter('.form__errors')->count());
        $this->assertSame('Cette valeur ne doit pas être vide.', $crawler->filter('#committee_feed_citizen_initiative_message .form__errors > li')->text());

        $this->client->submit($crawler->selectButton('Envoyer')->form([
            'committee_feed_citizen_initiative_message' => [
                'content' => 'Vraiment pas mal cette initiative',
                'published' => true,
            ],
        ]));

        $this->assertCount(1, $this->getMailjetEmailRepository()->findRecipientMessages(CommitteeCitizenInitiativeOrganizerNotificationMessage::class, 'jacques.picard@en-marche.fr'));
        $this->assertCount(1, $this->getMailjetEmailRepository()->findMessages(CommitteeCitizenInitiativeNotificationMessage::class, 'luciole1989@spambox.fr'));

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertTrue($this->seeMessageSuccesfullyCreatedFlash($crawler, 'Votre message a bien été publié.'));
        $this->assertContains('En Marche Paris 8', $crawler->filter('#committee-name')->text());
        $itemText = $crawler->filter('#committee-timeline .committee__timeline__item')->first()->text();
        $this->assertContains('Vraiment pas mal cette initiative', $itemText);
        $this->assertContains('Nettoyage de la ville', $itemText);
    }

    private function seeMessageSuccesfullyCreatedFlash(Crawler $crawler, ?string $message = null)
    {
        $flash = $crawler->filter('#notice-flashes');

        if ($message) {
            $this->assertSame($message, trim($flash->text()));
        }

        return 1 === count($flash);
    }

    public function testNotConnectedUserCannotSubscribeToAdherentActivity()
    {
        $initiative = $this->getCitizenInitiativeRepository()->findOneByUuid(LoadCitizenInitiativeData::CITIZEN_INITIATIVE_5_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/initiative-citoyenne/%s', $initiative->getSlug()));

        $this->assertSame(0, $crawler->filter('#activity_subscription')->count());

        $this->client->request(Request::METHOD_GET, sprintf('/initiative-citoyenne/%s/abonner', $initiative->getSlug()), [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $crawler = $this->client->followRedirect();

        $this->assertClientIsRedirectedToAuth();
    }

    public function testSubscribeToAdherentActivity()
    {
        $this->authenticateAsAdherent($this->client, 'damien.schmidt@example.ch', 'newpassword');

        $initiative = $this->getCitizenInitiativeRepository()->findOneByUuid(LoadCitizenInitiativeData::CITIZEN_INITIATIVE_5_UUID);
        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/initiative-citoyenne/%s', $initiative->getSlug()));

        $this->assertSame('Suivre', $crawler->filter('#activity_subscription a')->text());

        // User clicks on 'Suivre'
        $this->client->request(Request::METHOD_GET, sprintf('/initiative-citoyenne/%s/abonner', $initiative->getSlug()), [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/initiative-citoyenne/%s', $initiative->getSlug()));

        $this->assertSame('Ne plus suivre', $crawler->filter('#activity_subscription a')->text());

        // User clicks on 'Ne plus suivre'
        $this->client->request(Request::METHOD_GET, sprintf('/initiative-citoyenne/%s/abonner', $initiative->getSlug()), [], [], [
            'HTTP_X-Requested-With' => 'XMLHttpRequest',
        ]);
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->request(Request::METHOD_GET, sprintf('/initiative-citoyenne/%s', $initiative->getSlug()));

        $this->assertSame('Suivre', $crawler->filter('#activity_subscription a:contains("Suivre")')->text());
    }

    public function testRedirectionCitizenInitiativeFromOldUrl()
    {
        $this->client->request(Request::METHOD_GET, '/initiative-citoyenne/'.LoadCitizenInitiativeData::CITIZEN_INITIATIVE_4_UUID.'/'.date('Y-m-d', strtotime('+11 days')).'-nettoyage-de-la-ville');

        $this->assertStatusCode(Response::HTTP_MOVED_PERMANENTLY, $this->client);

        $this->assertClientIsRedirectedTo('/initiative-citoyenne/'.date('Y-m-d', strtotime('+11 days')).'-nettoyage-de-la-ville', $this->client);
        $this->client->followRedirect();

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadEventCategoryData::class,
            LoadCitizenInitiativeCategoryData::class,
            LoadEventData::class,
            LoadCitizenInitiativeData::class,
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

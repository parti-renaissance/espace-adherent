<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenInitiativeData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\Entity\CitizenInitiative;
use AppBundle\Entity\EventInvite;
use AppBundle\Mailjet\Message\CitizenInitiativeInvitationMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 */
class CitizenInitiativeControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    public function testAnonymousUserCannotCreateCitizenInitiative()
    {
        // Anonymous
        $this->client->request(Request::METHOD_GET, '/initiative_citoyenne/creer');

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('http://localhost/espace-adherent/connexion', $this->client);
    }

    public function testHostCannotCreateCitizenInitiative()
    {
        // Login as supervisor
        $crawler = $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(0, $crawler->filter('a:contains("Je crée mon initiative")')->count());

        $this->client->request(Request::METHOD_GET, '/initiative_citoyenne/creer');

        $this->assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());
    }

    public function testAdherentCreateCitizenInitiative()
    {
        // Login as Adherent not AL
        $crawler = $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertSame(2, $crawler->filter('a:contains("Je crée mon initiative")')->count());

        $this->client->click($crawler->selectLink('Je crée mon initiative')->link());

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('Je crée mon initiative citoyenne', $this->client->getResponse()->getContent());
    }

    public function testCreateCitizenInitiativeFailed()
    {
        $this->authenticateAsAdherent($this->client, 'michel.vasseur@example.ch', 'secret!12345');
        $this->client->request(Request::METHOD_GET, '/initiative_citoyenne/creer');

        $data = [];
        $this->client->submit($this->client->getCrawler()->selectButton('Je crée mon événement')->form(), $data);

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

        $this->client->request(Request::METHOD_GET, '/initiative_citoyenne/creer');

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
        $data['citizen_initiative']['address']['address'] = 'Pilgerweg 58';
        $data['citizen_initiative']['address']['cityName'] = 'Kilchberg';
        $data['citizen_initiative']['address']['postalCode'] = '8802';
        $data['citizen_initiative']['address']['country'] = 'CH';
        $data['citizen_initiative']['description'] = 'Mon initiative en Suisse';
        $data['citizen_initiative']['capacity'] = 15;
        $data['citizen_initiative']['expert_assistance_needed'] = 1;
        $data['citizen_initiative']['expert_assistance_description'] = 'J\'ai besoin d\'aide';
        $data['citizen_initiative']['coaching_requested'] = 1;
        $data['citizen_initiative']['coaching_request']['problem_description'] = 'Mon problème est ...';
        $data['citizen_initiative']['coaching_request']['proposed_solution'] = 'Voici ma proposition';
        $data['citizen_initiative']['coaching_request']['required_means'] = "Voilà ce dont j'ai besoin";
        $data['citizen_initiative']['interests'][] = 'agriculture';

        $this->client->submit($this->client->getCrawler()->selectButton('Je crée mon événement')->form(), $data);

        $initiative = $this->getCitizenInitiativeRepository()->findOneBy(['name' => 'Mon initiative']);

        $this->assertInstanceOf(CitizenInitiative::class, $initiative);
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo('/evenements', $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertSame(1, $crawler->filter('.search__results__meta h2 a:contains("Mon initiative")')->count());
    }

    public function testAdherentCanInviteToEvent()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');
        $initiative = $this->getCitizenInitiativeRepository()->findOneByUuid(LoadCitizenInitiativeData::CITIZEN_INITIATIVE_4_UUID);
        $initiativeUrl = sprintf('/initiative_citoyenne/%s/%s', LoadCitizenInitiativeData::CITIZEN_INITIATIVE_4_UUID, $slug = $initiative->getSlug());

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
        $initiativeUrl = sprintf('/initiative_citoyenne/%s/%s', LoadCitizenInitiativeData::CITIZEN_INITIATIVE_4_UUID, $slug = $initiative->getSlug());

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

        $this->client->request(Request::METHOD_GET, sprintf('/initiative_citoyenne/%s/%s/invitation/merci', LoadCitizenInitiativeData::CITIZEN_INITIATIVE_3_UUID, $initiative->getSlug()));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadEventCategoryData::class,
            LoadEventData::class,
            LoadCitizenInitiativeData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}

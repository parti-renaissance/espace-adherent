<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\DataFixtures\ORM\LoadNewsletterSubscriptionData;
use AppBundle\DataFixtures\ORM\LoadReferentManagedUserData;
use AppBundle\Entity\Event;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Config;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 * @group referent
 */
class ReferentControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider providePages
     */
    public function testReferentBackendIsForbiddenAsAnonymous($path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedToAuth();
    }

    /**
     * @dataProvider providePages
     */
    public function testReferentBackendIsForbiddenAsAdherentNotReferent($path)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    /**
     * @dataProvider providePages
     */
    public function testReferentBackendIsAccessibleAsReferent($path)
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function testCreateEventFailed()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');

        $this->client->request(Request::METHOD_GET, '/espace-referent/evenements/creer');

        $data = [];

        $this->client->submit($this->client->getCrawler()->selectButton('Créer cet événement')->form(), $data);
        $this->assertSame(4, $this->client->getCrawler()->filter('.form__errors')->count());

        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#committee-event-name-field > .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#committee-event-description-field > .form__errors > li')->text());
        $this->assertSame('L\'adresse est obligatoire.',
            $this->client->getCrawler()->filter('#committee-event-address-address-field > .form__errors > li')->text());
        $this->assertSame('Votre adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.',
            $this->client->getCrawler()->filter('#committee-event-address > .form__errors > li')->text());
    }

    public function testCreateEventSuccessful()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');

        $this->client->request(Request::METHOD_GET, '/espace-referent/evenements/creer');

        $data = [];
        $data['committee_event']['name'] = 'Premier événement';
        $data['committee_event']['category'] = 4;
        $data['committee_event']['beginAt']['date']['day'] = 14;
        $data['committee_event']['beginAt']['date']['month'] = 6;
        $data['committee_event']['beginAt']['date']['year'] = 2017;
        $data['committee_event']['beginAt']['time']['hour'] = 9;
        $data['committee_event']['beginAt']['time']['minute'] = 0;
        $data['committee_event']['finishAt']['date']['day'] = 15;
        $data['committee_event']['finishAt']['date']['month'] = 6;
        $data['committee_event']['finishAt']['date']['year'] = 2017;
        $data['committee_event']['finishAt']['time']['hour'] = 23;
        $data['committee_event']['finishAt']['time']['minute'] = 0;
        $data['committee_event']['address']['address'] = 'Pilgerweg 58';
        $data['committee_event']['address']['cityName'] = 'Kilchberg';
        $data['committee_event']['address']['postalCode'] = '8802';
        $data['committee_event']['address']['country'] = 'CH';
        $data['committee_event']['description'] = 'Premier événement en Suisse';
        $data['committee_event']['capacity'] = 100;

        $this->client->submit($this->client->getCrawler()->selectButton('Créer cet événement')->form(), $data);

        /** @var Event $event */
        $event = $this->getEventRepository()->findOneBy(['name' => 'Premier événement']);

        $this->assertInstanceOf(Event::class, $event);
        $this->assertClientIsRedirectedTo('/evenements/'.$event->getSlug(), $this->client);

        $this->client->followRedirect();
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertSame('Pilgerweg 58, 8802 Kilchberg, Suisse', $this->client->getCrawler()->filter('span.committee-event-address')->text());
        $this->assertSame('Mercredi 14 juin 2017, 9h00', $this->client->getCrawler()->filter('span.committee-event-date')->text());
        $this->assertSame('Premier événement en Suisse', $this->client->getCrawler()->filter('div.committee-event-description')->text());
        $this->assertContains('100 inscrits', $this->client->getCrawler()->filter('div.committee-event-attendees')->html());
    }

    public function testSearchUserToSendMail()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        $this->assertSame(6, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $data = [
            'n' => 1,
            'anc' => 1,
            'aic' => 1,
            'h' => 1,
            'pc' => 77,
        ];
        $this->client->submit($this->client->getCrawler()->selectButton('Filtrer')->form(), $data);
        $this->assertSame(2, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        $this->assertSame(6, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());

        $data = [
            'n' => 1,
            'anc' => 1,
            'aic' => 1,
            'h' => 1,
            's' => 1,
            'city' => 'Zürich, Kilchberg',
        ];
        $this->client->submit($this->client->getCrawler()->selectButton('Filtrer')->form(), $data);
        $this->assertSame(2, $this->client->getCrawler()->filter('tbody tr.referent__item')->count());
    }

    public function testCancelSendMail()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        $data = [
            'n' => 1,
            'anc' => 1,
            'aic' => 1,
            'h' => 1,
            's' => 1,
        ];
        $this->client->submit($this->client->getCrawler()->selectButton('Filtrer')->form(), $data);
        $this->client->click($this->client->getCrawler()->selectLink('Leur envoyer un message')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('http://'.Config::APP_HOST.'/espace-referent/utilisateurs/message', $this->client->getRequest()->getUri());

        $this->client->click($this->client->getCrawler()->selectLink('Annuler')->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertEquals('http://'.Config::APP_HOST.'/espace-referent/utilisateurs', $this->client->getRequest()->getUri());
    }

    public function testSendMailFailed()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        $data = [
            'n' => 1,
            'anc' => 1,
            'aic' => 1,
            'h' => 1,
            's' => 1,
        ];
        $this->client->submit($this->client->getCrawler()->selectButton('Filtrer')->form(), $data);
        $this->client->click($this->client->getCrawler()->selectLink('Leur envoyer un message')->link());

        $data = [];
        $this->client->submit($this->client->getCrawler()->selectButton('Envoyer le message')->form(), $data);

        $this->assertSame(2, $this->client->getCrawler()->filter('.form__errors')->count());
        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('label[for=referent_message_subject] + .form__errors > li')->text());
        $this->assertSame('Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('label[for=referent_message_content] + .form__errors > li')->text());
    }

    public function testSendMailSuccessful()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');
        $data = [
            'n' => 1,
            'anc' => 1,
            'aic' => 1,
        ];
        $this->client->submit($this->client->getCrawler()->selectButton('Filtrer')->form(), $data);
        $this->client->click($this->client->getCrawler()->selectLink('Leur envoyer un message')->link());
        $this->assertContains('Referent Referent', $this->client->getCrawler()->filter('form')->html());
        $this->assertContains('6 marcheurs(s)', $this->client->getCrawler()->filter('form')->html());

        $data = [];
        $data['referent_message']['subject'] = 'Event reminder';
        $data['referent_message']['content'] = 'One event is planned.';
        $this->client->submit($this->client->getCrawler()->selectButton('Envoyer le message')->form(), $data);

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertContains('http://'.Config::APP_HOST.'/espace-referent/utilisateurs?', $this->client->getRequest()->getUri());
    }

    public function testFilterAdherents()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');

        $this->client->request(Request::METHOD_GET, '/espace-referent/utilisateurs');

        $this->assertCount(6, $this->client->getCrawler()->filter('tbody tr.referent__item'));

        // filter hosts
        $data = [
            'h' => true,
            's' => false,
            'n' => false,
            'anc' => false,
            'aic' => false,
        ];

        $this->client->submit($this->client->getCrawler()->selectButton('Filtrer')->form(), $data);

        $this->assertContains('1 contact(s) trouvés', $this->client->getCrawler()->filter('.referent__filters__count')->text());
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item--host'));
        $this->assertContains('Gisele', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());
        $this->assertContains('Berthoux', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());

        // filter supervisors
        $data = [
            'h' => false,
            's' => true,
            'n' => false,
            'anc' => false,
            'aic' => false,
        ];

        $this->client->submit($this->client->getCrawler()->selectButton('Filtrer')->form(), $data);

        $this->assertContains('1 contact(s) trouvés', $this->client->getCrawler()->filter('.referent__filters__count')->text());
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item--host'));
        $this->assertContains('Francis', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());
        $this->assertContains('Brioul', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());

        // filter newsletter subscriptions
        $data = [
            'h' => false,
            's' => false,
            'n' => true,
            'anc' => false,
            'aic' => false,
        ];

        $this->client->submit($this->client->getCrawler()->selectButton('Filtrer')->form(), $data);

        $this->assertContains('2 contact(s) trouvés', $this->client->getCrawler()->filter('.referent__filters__count')->text());
        $this->assertCount(2, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(2, $this->client->getCrawler()->filter('tbody tr.referent__item--newsletter'));
        $this->assertContains('92110', $this->client->getCrawler()->filter('tbody tr.referent__item')->first()->text());
        $this->assertContains('77000', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(1)->text());

        // filter adherents in no committee
        $data = [
            'h' => false,
            's' => false,
            'n' => false,
            'anc' => true,
            'aic' => false,
        ];

        $this->client->submit($this->client->getCrawler()->selectButton('Filtrer')->form(), $data);

        $this->assertContains('3 contact(s) trouvés', $this->client->getCrawler()->filter('.referent__filters__count')->text());
        $this->assertCount(3, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(2, $this->client->getCrawler()->filter('tbody tr.referent__item--host'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item--adherent'));
        $this->assertContains('Francis', $this->client->getCrawler()->filter('tbody tr.referent__item')->first()->text());
        $this->assertContains('Brioul', $this->client->getCrawler()->filter('tbody tr.referent__item')->first()->text());
        $this->assertContains('Gisele', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertContains('Berthoux', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertContains('Michelle', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(2)->text());
        $this->assertContains('D.', $this->client->getCrawler()->filter('tbody tr.referent__item')->eq(2)->text());

        // filter adherents in committees
        $data = [
            'h' => false,
            's' => false,
            'n' => false,
            'anc' => false,
            'aic' => true,
        ];

        $this->client->submit($this->client->getCrawler()->selectButton('Filtrer')->form(), $data);

        $this->assertContains('1 contact(s) trouvés', $this->client->getCrawler()->filter('.referent__filters__count')->text());
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item'));
        $this->assertCount(1, $this->client->getCrawler()->filter('tbody tr.referent__item--adherent'));
        $this->assertContains('Michel', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());
        $this->assertContains('V.', $this->client->getCrawler()->filter('tbody tr.referent__item')->text());
    }

    public function providePages()
    {
        return [
            ['/espace-referent/utilisateurs'],
            ['/espace-referent/evenements'],
            ['/espace-referent/comites'],
            ['/espace-referent/evenements/creer'],
        ];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadEventCategoryData::class,
            LoadHomeBlockData::class,
            LoadNewsletterSubscriptionData::class,
            LoadReferentManagedUserData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}

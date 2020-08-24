<?php

namespace Tests\App\Controller\EnMarche\ElectedRepresentative;

use App\DataFixtures\ORM\LoadElectedRepresentativeData;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 */
class ReferentElectedRepresentativeControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testListElectedRepresentatives()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus');

        $this->assertCount(5, $crawler->filter('tbody tr.referent__item'));
        $this->assertCount(1, $crawler->filter('.status.status__1'));
        $this->assertContains('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Conseiller(e) municipal(e) (NC)Clichy (92110)', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Maire', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertNotContains('Président(e) d\'EPCI', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('PS (2016)', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Non', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('BOULON Daniel', $crawler->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertContains('Conseiller(e) municipal(e) (DIV)Rouen (76000)', $crawler->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertContains(' G.s (2018) PS (2014 à 2018)', preg_replace('/\s+/', ' ', $crawler->filter('tbody tr.referent__item')->eq(1)->filter('td')->eq(4)->text()));
        $this->assertContains('Non', $crawler->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertContains('DUFOUR Michelle', $crawler->filter('tbody tr.referent__item')->eq(2)->text());
        $this->assertContains('Oui', $crawler->filter('tbody tr.referent__item')->eq(2)->text());
        $this->assertContains('LOBELL', $crawler->filter('tbody tr.referent__item')->eq(3)->text());
    }

    public function testFilterElectedRepresentatives()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus');

        $this->assertCount(5, $crawler->filter('tbody tr.referent__item'));

        // filter by lastname
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[lastName]'] = 'BOU';

        $crawler = $this->client->submit($form);

        $this->assertCount(2, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('BOULON Daniel', $crawler->filter('tbody tr.referent__item')->eq(1)->text());

        // filter by firstname
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[lastName]'] = 'BOU';
        $form['f[firstName]'] = 'Delphine';

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());

        // filter by labels
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[lastName]'] = '';
        $form['f[firstName]'] = '';
        $form['f[labels]'] = ['PS', 'LaREM'];

        $crawler = $this->client->submit($form);

        $this->assertCount(2, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('DUFOUR Michelle', $crawler->filter('tbody tr.referent__item')->eq(1)->text());

        // filter by politicalFunctions
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[labels]'] = ['PS', 'LaREM'];
        $form['f[politicalFunctions]'] = ['mayor'];

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());

        // filter by mandates
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[labels]'] = [];
        $form['f[politicalFunctions]'] = [];
        $form['f[mandates]'] = ['membre_EPCI', 'depute'];

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('LOBELL André', $crawler->filter('tbody tr.referent__item')->eq(0)->text());

        // filter by gender
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[mandates]'] = [];
        $form['f[gender]'] = 'female';

        $crawler = $this->client->submit($form);

        $this->assertCount(2, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOUILLOUX Delphine', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('DUFOUR Michelle', $crawler->filter('tbody tr.referent__item')->eq(1)->text());

        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[gender]'] = 'unknown';

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOULON Daniel', $crawler->filter('tbody tr.referent__item')->eq(0)->text());

        // filter not adherents
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[gender]'] = '';
        $form['f[contactType]'] = 'other';

        $crawler = $this->client->submit($form);

        $this->assertCount(4, $crawler->filter('tbody tr.referent__item'));

        // filter by cities
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[contactType]'] = 'other';
        $form['f[cities]'] = [6];

        $crawler = $this->client->submit($form);

        $this->assertCount(2, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOULON Daniel', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('LOBELL André', $crawler->filter('tbody tr.referent__item')->eq(1)->text());

        // filter by userListDefinitions
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[contactType]'] = 'other';
        $form['f[cities]'] = [6];
        $form['f[userListDefinitions]'] = [3];

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('BOULON Daniel', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
    }

    public function testListElectedRepresentativesForParis()
    {
        $this->authenticateAsAdherent($this->client, 'referent-75-77@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus');

        $text = $crawler->filter('tbody')->text();

        $this->assertCount(3, $crawler->filter('tr.referent__item'));

        $this->assertContains('PARIS Département', $text);
        $this->assertContains('PARIS Arrondissement', $text);
        $this->assertContains('PARISS Circonscription', $text);
    }

    public function testShowElectedRepresentative()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus/'.LoadElectedRepresentativeData::ELECTED_REPRESENTATIVE_1_UUID);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertContains('Michelle DUFOUR', $crawler->filter('.elected-representative-identity')->eq(0)->text());
        $this->assertContains('François Hollande', $crawler->filter('.elected-representative-sponsorships tbody tr')->eq(0)->text());
        $this->assertContains('Emmanuel Macron', $crawler->filter('.elected-representative-sponsorships tbody tr')->eq(1)->text());
        $this->assertCount(1, $crawler->filter('.elected-representative-mandates tbody tr'));
        $this->assertContains('Conseiller(e) municipal(e)', $crawler->filter('.elected-representative-mandates tbody tr')->text());
        $this->assertContains('REM', $crawler->filter('.elected-representative-mandates tbody tr')->text());
        $this->assertContains('Soutien officiel', $crawler->filter('.elected-representative-mandates tbody tr')->text());
        $this->assertContains('23 juillet 2019', $crawler->filter('.elected-representative-mandates tbody tr')->text());
        $this->assertContains(' Autre membre (2019) Président(e) d\'EPCI (2015)', preg_replace('/\s+/', ' ', $crawler->filter('.elected-representative-mandates tbody tr')->eq(0)->filter('td')->eq(5)->text()));
        $this->assertCount(1, $crawler->filter('.elected-representative-labels tbody tr'));
        $this->assertNotContains('Aucune', $crawler->filter('.elected-representative-labels tbody tr')->text());
        $this->assertCount(0, $crawler->filter('.sponsorships tbody tr'));
        $this->assertCount(1, $crawler->filter('.elected-representative-candidacies tbody tr'));
        $this->assertContains('Aucune', $crawler->filter('.elected-representative-candidacies tbody tr')->text());

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus/'.LoadElectedRepresentativeData::ELECTED_REPRESENTATIVE_2_UUID);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertContains('Delphine BOUILLOUX', $crawler->filter('.elected-representative-identity')->eq(0)->text());
        $this->assertCount(5, $crawler->filter('.elected-representative-social-networks a'));

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/elus/'.LoadElectedRepresentativeData::ELECTED_REPRESENTATIVE_3_UUID);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $this->assertContains('Daniel BOULON', $crawler->filter('.elected-representative-identity')->eq(0)->text());
        $this->assertCount(1, $crawler->filter('.elected-representative-mandates tbody tr'));
        $this->assertCount(2, $crawler->filter('.elected-representative-labels tbody tr'));
        $this->assertCount(0, $crawler->filter('.sponsorships tbody tr'));
        $this->assertCount(1, $crawler->filter('.elected-representative-candidacies tbody tr'));
        $this->assertContains('Membre d\'EPCI', $crawler->filter('.elected-representative-candidacies tbody tr')->text());
        $this->assertContains('DIV', $crawler->filter('.elected-representative-candidacies tbody tr')->text());
        $this->assertContains('Pas soutenu', $crawler->filter('.elected-representative-candidacies tbody tr')->text());
        $this->assertContains('11 janvier 2017', $crawler->filter('.elected-representative-candidacies tbody tr')->text());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->disableRepublicanSilence();
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}

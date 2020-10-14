<?php

namespace Tests\App\Controller\EnMarche\TerritorialCouncil;

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

    public function testListTerritorialCouncilMembers()
    {
        $this->authenticateAsAdherent($this->client, 'referent-75-77@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/instances/membres');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(8, $crawler->filter('tbody tr.referent__item'));
        $this->assertCount(8, $crawler->filter('.status.status__1'));
        $this->assertContains('Berthoux Gisele', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Conseiller(ère) départemental(e) 75', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Conseiller(ère) consulaire 75', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Conseiller(ère) municipal(e) Paris 75010', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('désignée', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('02/02/2020', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('+33 1 38 76 43 34', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Non', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Non-abonné(e)', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Président(e) du groupe d\'opposition LaREM', $crawler->filter('tbody tr.referent__item')->eq(5)->text());
    }

    public function testFilterTerritorialCouncilMembers()
    {
        $this->authenticateAsAdherent($this->client, 'referent-75-77@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/instances/membres');

        $this->assertCount(8, $crawler->filter('tbody tr.referent__item'));

        // filter by lastname
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[lastName]'] = 'ar';

        $crawler = $this->client->submit($form);

        $this->assertCount(2, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('PARIS I Député', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Picard Jacques', $crawler->filter('tbody tr.referent__item')->eq(1)->text());

        // filter by firstname
        $form = $this->client->getCrawler()->selectButton('Appliquer')->form();
        $form['f[firstName]'] = 'Jacques';

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('Picard Jacques', $crawler->filter('tbody tr.referent__item')->eq(0)->text());

        // filter by gender
        $form['f[firstName]'] = '';
        $form['f[lastName]'] = '';
        $form['f[gender]'] = 'female';

        $crawler = $this->client->submit($form);

        $this->assertCount(3, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('Berthoux Gisele', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Olivera Lucie', $crawler->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertContains('Referent75and77', $crawler->filter('tbody tr.referent__item')->eq(2)->text());

        // filter by email status
        $form['f[emailSubscription]'] = 0;

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('Berthoux Gisele', $crawler->filter('tbody tr.referent__item')->eq(0)->text());

        // filter by age
        $form['f[emailSubscription]'] = '';
        $form['f[gender]'] = '';
        $form['f[ageMin]'] = 29;
        $form['f[ageMax]'] = 41;

        $crawler = $this->client->submit($form);

        $this->assertCount(4, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('Berthoux Gisele', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Duroc Benjamin', $crawler->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertContains('Olivera Lucie', $crawler->filter('tbody tr.referent__item')->eq(2)->text());
        $this->assertContains('PARIS I Député', $crawler->filter('tbody tr.referent__item')->eq(3)->text());

        // by territorial council
        $form['f[ageMin]'] = '';
        $form['f[ageMax]'] = '';
        $form['f[referentTags]'] = [100];

        $crawler = $this->client->submit($form);

        $this->assertCount(8, $crawler->filter('tbody tr.referent__item'));

        $form['f[referentTags]'] = [102];

        $crawler = $this->client->submit($form);

        $this->assertCount(0, $crawler->filter('tbody tr.referent__item'));

        // filter by qualities
        $form['f[referentTags]'] = [100, 102];
        $form['f[qualities]'] = ['mayor', 'committee_supervisor'];

        $crawler = $this->client->submit($form);

        $this->assertCount(4, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('Kiroule Pierre', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('PARIS I Député', $crawler->filter('tbody tr.referent__item')->eq(1)->text());
        $this->assertContains('Referent Referent', $crawler->filter('tbody tr.referent__item')->eq(2)->text());
        $this->assertContains('Referent75and77', $crawler->filter('tbody tr.referent__item')->eq(3)->text());

        // filter by qualities PC
        $form['f[qualities]'] = ['PC_department_councilor'];

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('Picard Jacques', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Adhérent(e) désigné(e) Super comité de Paris', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
        $this->assertContains('Conseiller(ère) départemental(e) 75', $crawler->filter('tbody tr.referent__item')->eq(0)->text());

        // filter by cities
        $form['f[qualities]'] = [];
        $form['f[cities]'] = [5];

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('PARIS I Député', $crawler->filter('tbody tr.referent__item')->eq(0)->text());

        // filter by committees
        $form['f[cities]'] = [];
        $form['f[committees]'] = [1];

        $crawler = $this->client->submit($form);

        $this->assertCount(1, $crawler->filter('tbody tr.referent__item'));
        $this->assertContains('Referent75and77', $crawler->filter('tbody tr.referent__item')->eq(0)->text());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}

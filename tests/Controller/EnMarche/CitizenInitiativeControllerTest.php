<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenInitiativeData;
use AppBundle\DataFixtures\ORM\LoadEventCategoryData;
use AppBundle\DataFixtures\ORM\LoadEventData;
use AppBundle\Entity\CitizenInitiative;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 */
class CitizenInitiativeControllerTest extends SqliteWebTestCase
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
        $this->client->request(Request::METHOD_GET, '/initiative_citoyenne/creer');

        $data = [];
        $data['citizen_initiative']['name'] = 'Mon initiative';
        $data['citizen_initiative']['category'] = 4;
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
        $data['citizen_initiative']['description'] = 'Ma initiative en Suisse';
        $data['citizen_initiative']['expert_assistance_needed'][] = 'Oui';
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

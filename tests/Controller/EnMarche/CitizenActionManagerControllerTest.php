<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenActionCategoryData;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use AppBundle\Entity\CitizenAction;
use AppBundle\Mailer\Message\CitizenActionCreationConfirmationMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 */
class CitizenActionManagerControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    public function testCreateCitizenActionIsForbiddenIfUserIsNotProjectOrganizer()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-paris-8/actions/creer');

        static::assertResponseStatusCode(Response::HTTP_FORBIDDEN, $this->client->getResponse());

        $crawler = $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-paris-8');
        $this->assertSame(0, $crawler->selectLink('Créer une action citoyenne')->count());
    }

    public function testCreateCitizeActionIsForbiddenIfProjectIsNotApproved()
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-marseille/actions/creer');

        static::assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
        $crawler = $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-paris-8');
        $this->assertSame(0, $crawler->selectLink('Créer une action citoyenne')->count());
    }

    public function testCreateCitizenActionFailed()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');
        $crawler = $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-paris-8');
        $this->assertSame(1, $crawler->selectLink('Créer une action citoyenne')->count());

        $this->client->click($crawler->selectLink('Créer une action citoyenne')->link());

        $this->assertSame('/projets-citoyens/le-projet-citoyen-a-paris-8/actions/creer', $this->client->getRequest()->getPathInfo());

        $this->client->submit($this->client->getCrawler()->selectButton('Je crée mon action citoyenne')->form());

        $this->assertSame(4, $this->client->getCrawler()->filter('.form__errors')->count());
        $this->assertSame(
            'Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#citizen-action-name-field > .form__errors > li')->text()
        );
        $this->assertSame(
            'Cette valeur ne doit pas être vide.',
            $this->client->getCrawler()->filter('#citizen-action-description-field > .form__errors > li')->text()
        );
        $this->assertSame(
            'Votre adresse n\'est pas reconnue. Vérifiez qu\'elle soit correcte.',
            $this->client->getCrawler()->filter('#citizen-action-address > .form__errors > li')->text()
        );
        $this->assertSame(
            'L\'adresse est obligatoire.',
            $this->client->getCrawler()->filter('#citizen-action-address-address-field > .form__errors > li')->text()
        );

        $data = [];
        $data['citizen_action']['name'] = 'n';
        $data['citizen_action']['description'] = 'a';
        $this->client->submit($this->client->getCrawler()->selectButton('Je crée mon action citoyenne')->form(), $data);

        $this->assertSame(4, $this->client->getCrawler()->filter('.form__errors')->count());
        $this->assertSame(
            'Vous devez saisir au moins 5 caractères.',
            $this->client->getCrawler()->filter('#citizen-action-name-field > .form__errors > li')->text()
        );
        $this->assertSame(
            'Vous devez saisir au moins 10 caractères.',
            $this->client->getCrawler()->filter('#citizen-action-description-field > .form__errors > li')->text()
        );

        // Check that "Action citoyenne" is the only category choice and is pre selected
        $category = $this->client->getCrawler()->filter('#citizen_action_category > option');

        $this->assertCount(1, $category);
        $this->assertSame('Action citoyenne', $category->text());
        $this->assertSame('selected', $category->attr('selected'));
    }

    public function testCreateCitizenActionSuccessful()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');
        $crawler = $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-paris-8');
        $this->assertSame(1, $crawler->selectLink('Créer une action citoyenne')->count());

        $this->client->click($crawler->selectLink('Créer une action citoyenne')->link());

        $this->assertSame('/projets-citoyens/le-projet-citoyen-a-paris-8/actions/creer', $this->client->getRequest()->getPathInfo());

        $crawler = $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-paris-8/actions/creer');

        $data['citizen_action']['name'] = 'Mon action citoyenne';
        $data['citizen_action']['description'] = 'Ma première action citoyenne';
        $data['citizen_action']['address']['address'] = '44 rue des Courcelles';
        $data['citizen_action']['address']['postalCode'] = '75008';
        $data['citizen_action']['address']['cityName'] = 'Paris';
        $data['citizen_action']['address']['country'] = 'FR';

        $this->client->submit($crawler->selectButton('Je crée mon action citoyenne')->form(), $data);

        $this->assertSame(0, $this->client->getCrawler()->filter('.form__errors')->count());

        $this->assertInstanceOf(CitizenAction::class, $this->getCitizenActionRepository()->findOneBy(['slug' => (new \DateTime())->format('Y-m-d').'-mon-action-citoyenne']));

        $this->assertCount(1, $this->getEmailRepository()->findRecipientMessages(CitizenActionCreationConfirmationMessage::class, 'jacques.picard@en-marche.fr'));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadCitizenProjectData::class,
            LoadCitizenActionCategoryData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}

<?php

namespace Tests\AppBundle\Controller\EnMarche;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group citizenProject
 */
class CitizenProjectMediaGeneratorControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->init();
    }

    protected function tearDown(): void
    {
        $this->kill();

        parent::tearDown();
    }

    public function testCoverImageIsGeneratedSuccessfully(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request('GET', '/projets-citoyens/75008-le-projet-citoyen-a-paris-8/media-generateur/images');

        $crawler = $this->client->submit($crawler->selectButton('Aperçu')->form([
            'citizen_project_media[citizenProjectTitle]' => 'Mon super projet!',
            'citizen_project_media[emoji]' => '😊',
            'citizen_project_media[backgroundColor]' => '#6f80ff',
            'citizen_project_media[city]' => 'Paris',
            'citizen_project_media[departmentCode]' => 75,
            'citizen_project_media[backgroundImage]' => __DIR__.'/../../../app/data/static/bercy-banner.jpg',
        ]));

        $this->isSuccessful($this->client->getResponse());

        $this->assertContains(
            'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAzQAAAE4CAYAAACNL7YlAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAALiQAAC4kBN8nLrQAAIABJREFUeAEAKoDVfwFGTpn/AAEAAAEAAQAAAQEAAQEAAAEAAQAAAQEAAQAAAAAAAAAAAAAAAAAAAAAAAAABAAAAAQEBAAAAAAAAAAAAAAAAAAAAAAAAAAAA',
            $crawler->selectImage('Cover Facebook image')->image()->getUri()
        );
    }

    public function testTractPdfFileIsGeneratedSuccessfully(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request('GET', '/projets-citoyens/75008-le-projet-citoyen-a-paris-8/media-generateur/tracts');

        $this->client->submit($crawler->selectButton('Créer mon PDF')->form([
            'citizen_project_media[citizenProjectTitle]' => 'Mon super projet!',
            'citizen_project_media[backgroundColor]' => '#6f80ff',
            'citizen_project_media[description]' => 'Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression. Le Lorem Ipsum est le faux texte standard de l\'imprimerie depuis les années 1500, quand un peintre anonyme assembla ensemble des morceaux de texte pour réaliser un livre spécimen de polices de texte.',
            'citizen_project_media[details]' => 'Le Lorem Ipsum est simplement du faux texte employé dans la composition et la mise en page avant impression. Le Lorem Ipsum est le faux texte standard de l\'imprimerie depuis les années 1500, quand un peintre anonyme assembla ensemble des morceaux de texte pour réaliser un livre spécimen de polices de texte.',
            'citizen_project_media[backgroundImage]' => __DIR__.'/../../../app/data/static/bercy-banner.jpg',
        ]));

        $response = $this->client->getResponse();

        $this->isSuccessful($response);
        $this->assertArrayHasKey('content-disposition', $response->headers->all());
        $this->assertContains('attachment; filename="tract_', $response->headers->get('content-disposition'));
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }
}

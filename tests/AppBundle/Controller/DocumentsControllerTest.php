<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\SqliteWebTestCase;

class DocumentsControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /**
     * @group functionnal
     */
    public function testDocumentsIndexAsAnonymous()
    {
        $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');
        $this->assertClientIsRedirectedTo('http://localhost/espace-adherent/connexion', $this->client);
    }

    /**
     * @group functionnal
     */
    public function testDocumentsIndexAsAdherent()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('h1:contains("Documents")'));
        $this->assertCount(1, $crawler->filter('h2:contains("Documents adhérents")'));
        $this->assertCount(0, $crawler->filter('h2:contains("Documents animateurs")'));
        $this->assertCount(0, $crawler->filter('h2:contains("Documents référents")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir1")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir2")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("document-adherent-c.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("dir3")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-host-b.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("dir4")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-referent-a.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-referent-b.pdf")'));
    }

    /**
     * @group functionnal
     */
    public function testDocumentsIndexAsHost()
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('h1:contains("Documents")'));
        $this->assertCount(1, $crawler->filter('h2:contains("Documents adhérents")'));
        $this->assertCount(1, $crawler->filter('h2:contains("Documents animateurs")'));
        $this->assertCount(0, $crawler->filter('h2:contains("Documents référents")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir1")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir2")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("document-adherent-c.pdf")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir3")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("document-host-b.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("dir4")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-referent-a.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-referent-b.pdf")'));
    }

    /**
     * @group functionnal
     */
    public function testDocumentsIndexAsReferent()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('h1:contains("Documents")'));
        $this->assertCount(1, $crawler->filter('h2:contains("Documents adhérents")'));
        $this->assertCount(0, $crawler->filter('h2:contains("Documents animateurs")'));
        $this->assertCount(1, $crawler->filter('h2:contains("Documents référents")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir1")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir2")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("document-adherent-c.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("dir3")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-host-b.pdf")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("dir4")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("document-referent-a.pdf")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("document-referent-b.pdf")'));
    }

    /**
     * @group functionnal
     */
    public function testDocumentsDirectory()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $link = $crawler->filter('ul.documents__tree li a:contains("dir2")');
        $this->assertCount(1, $link);

        $crawler = $this->client->click($link->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->assertCount(1, $crawler->filter('h1:contains("Documents")'));
        $this->assertCount(1, $crawler->filter('h2:contains("Documents adhérents")'));
        $this->assertCount(0, $crawler->filter('h2:contains("Documents animateurs")'));
        $this->assertCount(0, $crawler->filter('h2:contains("Documents référents")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("subdir")'));
        $this->assertCount(1, $crawler->filter('ul.documents__tree li a:contains("document-adherent-b.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("dir1")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("dir2")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("dir3")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-host-b.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("dir4")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-referent-a.pdf")'));
        $this->assertCount(0, $crawler->filter('ul.documents__tree li a:contains("document-referent-b.pdf")'));
    }

    /**
     * @group functionnal
     */
    public function testDocumentsDirectoryUnauthorized()
    {
        $documentsRoot = '/espace-adherent/documents/dossier';

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/animateurs/dir3');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/referents/dir4');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());

        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/referents/dir4');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());

        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/animateurs/dir3');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    /**
     * @group functionnal
     */
    public function testDocumentsRead()
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-adherent/documents');
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $link = $crawler->filter('ul.documents__tree li a:contains("document-adherent-c.pdf")');
        $this->assertCount(1, $link);

        $this->client->click($link->link());
        $this->assertResponseStatusCode(Response::HTTP_OK, $response = $this->client->getResponse());
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }

    /**
     * @group functionnal
     */
    public function testDocumentsReadUnauthorized()
    {
        $documentsRoot = '/espace-adherent/documents/telecharger';

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/animateurs/document-host-b.pdf');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());

        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/referents/document-host-b.pdf');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());

        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr', 'changeme1337');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/referents/document-referent-b.pdf');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());

        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');
        $this->client->request(Request::METHOD_GET, $documentsRoot.'/animateurs/document-host-b.pdf');
        $this->assertResponseStatusCode(Response::HTTP_NOT_FOUND, $this->client->getResponse());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}

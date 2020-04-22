<?php

namespace Tests\AppBundle\Controller\EnMarche;

use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group certification
 */
class CertificationRequestControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testApprovedCertificationRequest(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification');
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertContains('Demande de certification confirmée', $crawler->filter('.certification-status')->text());

        $this->assertCertificationRequestIsDisabled();
    }

    public function testPendingCertificationRequest(): void
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr');

        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification');
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertContains('Demande de certification en attente', $crawler->filter('.certification-status')->text());

        $this->assertCertificationRequestIsDisabled();
    }

    public function testRefusedCertificationRequest(): void
    {
        $this->authenticateAsAdherent($this->client, 'luciole1989@spambox.fr');

        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification');
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertContains('Demande de certification refusée', $crawler->filter('.certification-status')->text());

        $this->assertCertificationRequestIsSuccessful();
    }

    public function testBlockedCertificationRequest(): void
    {
        $this->authenticateAsAdherent($this->client, 'gisele-berthoux@caramail.com');

        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification');
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertContains('Demande de certification bloquée', $crawler->filter('.certification-status')->text());

        $this->assertCertificationRequestIsDisabled();
    }

    public function testNoCertificationRequest(): void
    {
        $this->authenticateAsAdherent($this->client, 'kiroule.p@blabla.tld');

        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification');
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertContains('Votre profil n\'est pas encore certifié.', $crawler->filter('.certification-status')->text());

        $this->assertCertificationRequestIsSuccessful();
    }

    private function assertCertificationRequestIsDisabled(): void
    {
        $this->client->request('GET', '/espace-adherent/mon-compte/certification/demande');
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-compte/certification', $this->client);

        $this->client->followRedirect();
        $this->assertResponseStatusCode(200, $this->client->getResponse());
    }

    private function assertCertificationRequestIsSuccessful(): void
    {
        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification/demande');
        $this->assertResponseStatusCode(200, $this->client->getResponse());

        $form = $crawler->filter('form[name="certification_request"]')->form();
        $this->client->request('POST', '/espace-adherent/mon-compte/certification/demande', [
            'certification_request' => [
                '_token' => $form['certification_request[_token]']->getValue(),
            ],
        ], [
            'certification_request' => [
                'document' => new UploadedFile(
                    __DIR__.'/../../../app/data/files/application_requests/curriculum/cv.pdf',
                    'cv.pdf',
                    'application/pdf',
                    1234
                ),
            ],
        ]);
        $this->assertClientIsRedirectedTo('/espace-adherent/mon-compte/certification', $this->client);

        $crawler = $this->client->followRedirect();
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertContains('Votre demande de certification a bien été enregistrée.', $crawler->filter('.flash')->text());
        $this->assertContains('Demande de certification en attente', $crawler->filter('.certification-status')->text());
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

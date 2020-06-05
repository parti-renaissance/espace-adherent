<?php

namespace Tests\App\Controller\EnMarche;

use App\Entity\CertificationRequest;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group certification
 */
class CertificationRequestControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    private $certificationRequestRepository;

    public function testApprovedCertificationRequest(): void
    {
        $this->authenticateAsAdherent($this->client, 'jacques.picard@en-marche.fr');

        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification');
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertContains('Votre profil est certifié', $crawler->filter('.certification-status')->text());

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
        $this->authenticateAsAdherent($this->client, $email = 'luciole1989@spambox.fr');
        $this->assertEquals(2, $this->countCertificationRequests($email));

        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification');
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertContains('Demande de certification refusée', $crawler->filter('.certification-status')->text());

        $this->assertCertificationRequestIsSuccessful();
        $this->assertEquals(3, $this->countCertificationRequests($email));
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
        $this->authenticateAsAdherent($this->client, $email = 'kiroule.p@blabla.tld');
        $this->assertEquals(0, $this->countCertificationRequests($email));

        $crawler = $this->client->request('GET', '/espace-adherent/mon-compte/certification');
        $this->assertResponseStatusCode(200, $this->client->getResponse());
        $this->assertContains('Votre profil n\'est pas encore certifié.', $crawler->filter('.certification-status')->text());

        $this->assertCertificationRequestIsSuccessful();
        $this->assertEquals(1, $this->countCertificationRequests($email));
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

        $this->certificationRequestRepository = $this->getRepository(CertificationRequest::class);
    }

    protected function tearDown()
    {
        $this->certificationRequestRepository = null;

        $this->kill();

        parent::tearDown();
    }

    private function countCertificationRequests(string $email): int
    {
        return $this
            ->certificationRequestRepository
            ->createQueryBuilder('cr')
            ->select('COUNT(cr)')
            ->innerJoin('cr.adherent', 'adherent')
            ->where('adherent.emailAddress = :email')
            ->setParameter('email', $email)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }
}

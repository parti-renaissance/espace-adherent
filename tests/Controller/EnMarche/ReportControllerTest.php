<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadCitizenProjectData;
use Symfony\Component\HttpFoundation\Request;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MysqlWebTestCase;

/**
 * @group functional
 */
class ReportControllerTest extends MysqlWebTestCase
{
    use ControllerTestTrait;

    public function testAdherentCanReportCitizenProject(): void
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');
        $this->client->request(Request::METHOD_GET, '/projets-citoyens/le-projet-citoyen-a-paris-8');
        $this->isSuccessful($this->client->getResponse());

        $this->client->click($this->client->getCrawler()->selectLink('Signaler')->link());
        $this->assertSame('http://'.$this->hosts['app'].'/report/citizen-project/aa364092-3999-4102-930c-f711ef971195?redirectUrl=/projets-citoyens/le-projet-citoyen-a-paris-8', $this->client->getCrawler()->getUri());
        $this->isSuccessful($this->client->getResponse());

        $this->client->submit($this->client->getCrawler()->selectButton('Envoyer mon signalement')->form([
            'report_command' => [
                'reasons' => [3 => 'other'],
                'comment' => 'Ce projet n\'est pas conforme',
            ],
        ]));
        $this->assertClientIsRedirectedTo('/projets-citoyens/le-projet-citoyen-a-paris-8', $this->client);
    }

    public function testAdherentIsRedirectedToWebRootIfRedirectUrlIsNotAValidInternalPath(): void
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');
        $this->client->request(Request::METHOD_GET, '/report/citizen-project/aa364092-3999-4102-930c-f711ef971195?redirectUrl=http%3A%2F%2Fje-te-hack.com');
        $this->isSuccessful($this->client->getResponse());

        $this->client->submit($this->client->getCrawler()->selectButton('Envoyer mon signalement')->form([
            'report_command' => [
                'reasons' => [0 => 'en_marche_values', 1 => 'inappropriate'],
            ],
        ]));
        $this->assertClientIsRedirectedTo('/', $this->client);
    }

    public function testAdherentIsRedirectedWebRootIfNoRedirectUrlIsProvided(): void
    {
        $this->authenticateAsAdherent($this->client, 'benjyd@aol.com', 'HipHipHip');
        $this->client->request(Request::METHOD_GET, '/report/citizen-project/aa364092-3999-4102-930c-f711ef971195');
        $this->isSuccessful($this->client->getResponse());

        $this->client->submit($this->client->getCrawler()->selectButton('Envoyer mon signalement')->form([
            'report_command' => [
                'reasons' => [0 => 'en_marche_values'],
            ],
        ]));
        $this->assertClientIsRedirectedTo('/', $this->client);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadCitizenProjectData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}

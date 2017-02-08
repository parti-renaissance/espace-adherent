<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\SqliteWebTestCase;

class ReferentControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /**
     * @group functionnal
     * @dataProvider providePages
     */
    public function testReferentBackendIsForbiddenAsAnonymous($path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedTo('http://localhost/espace-adherent/connexion', $this->client);
    }

    /**
     * @group functionnal
     * @dataProvider providePages
     */
    public function testReferentBackendIsForbiddenAsAdherentNotReferent($path)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    /**
     * @group functionnal
     * @dataProvider providePages
     */
    public function testReferentBackendIsAccessibleAsReferent($path)
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
    }

    public function providePages()
    {
        return [
            ['/referent/utilisateurs'],
            ['/referent/utilisateurs/envoyer-un-message/marcheurs'],
            ['/referent/utilisateurs/envoyer-un-message/adherents'],
            ['/referent/utilisateurs/envoyer-un-message/membres-comites'],
            ['/referent/utilisateurs/envoyer-un-message/animateurs-comites'],
            ['/referent/utilisateurs/envoyer-un-message/code-postal'],
            ['/referent/evenements'],
            ['/referent/evenements/creer'],
            ['/referent/comites'],
        ];
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

<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadNewsletterSubscriptionData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functionnal
 */
class ReferentControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /**
     * @dataProvider providePages
     */
    public function testReferentBackendIsForbiddenAsAnonymous($path)
    {
        $this->client->request(Request::METHOD_GET, $path);
        $this->assertClientIsRedirectedTo('http://localhost/espace-adherent/connexion', $this->client);
    }

    /**
     * @dataProvider providePages
     */
    public function testReferentBackendIsForbiddenAsAdherentNotReferent($path)
    {
        $this->authenticateAsAdherent($this->client, 'carl999@example.fr', 'secret!12345');

        $this->client->request(Request::METHOD_GET, $path);
        $this->assertStatusCode(Response::HTTP_FORBIDDEN, $this->client);
    }

    /**
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
            ['/espace-referent/utilisateurs'],
            ['/espace-referent/utilisateurs/envoyer-un-message/marcheurs'],
            ['/espace-referent/utilisateurs/envoyer-un-message/adherents'],
            ['/espace-referent/utilisateurs/envoyer-un-message/non-membres-comites'],
            ['/espace-referent/utilisateurs/envoyer-un-message/membres-comites'],
            ['/espace-referent/utilisateurs/envoyer-un-message/animateurs-comites'],
            ['/espace-referent/evenements'],
            ['/espace-referent/comites'],
            ['/espace-referent/evenements/creer'],
        ];
    }

    public function testReferentSendMessage()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');

        $crawler = $this->client->request(
            Request::METHOD_POST,
            '/espace-referent/utilisateurs/envoyer-un-message/selectionnes',
            [
                'selected_users_json' => implode(',', [
                    'a|1',
                    'a|7',
                    'n|4',
                    'n|3',
                    'a|5',
                ]),
            ]
        );

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->submit($crawler->filter('form[name=referent_message]')->form([
            'referent_message[subject]' => 'Un superbe sujet de message',
            'referent_message[content]' => 'Un superbe contenu de message !',
        ]));

        $this->assertClientIsRedirectedTo('/espace-referent/utilisateurs', $this->client);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadAdherentData::class,
            LoadNewsletterSubscriptionData::class,
        ]);
    }

    protected function tearDown()
    {
        $this->kill();

        parent::tearDown();
    }
}

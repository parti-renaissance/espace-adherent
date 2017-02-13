<?php

namespace Tests\AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadAdherentData;
use AppBundle\DataFixtures\ORM\LoadNewsletterSubscriptionData;
use AppBundle\Mailjet\Message\ReferentMessage;
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

    /**
     * @group functionnal
     */
    public function testReferentSendMessage()
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr', 'referent');

        $crawler = $this->client->request(
            Request::METHOD_POST,
            '/referent/utilisateurs/envoyer-un-message/selectionnes',
            [
                'selected' => [
                    [
                        'id' => '1',
                        'type' => 'adherent',
                    ],
                    [
                        'id' => '7',
                        'type' => 'adherent',
                    ],
                    [
                        'id' => '4',
                        'type' => 'newsletter_subscriber',
                    ],
                    [
                        'id' => '3',
                        'type' => 'newsletter_subscriber',
                    ],
                    [
                        'id' => '5',
                        'type' => 'adherent',
                    ],
                ],
            ]
        );

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $this->client->submit($crawler->filter('form[name=referent_message]')->form([
            'referent_message[subject]' => 'Un superbe sujet de message',
            'referent_message[content]' => 'Un superbe contenu de message !',
        ]));

        $this->assertClientIsRedirectedTo('/referent/utilisateurs', $this->client);

        // 3 emails should have been sent: one for the referent and one for each subscriber
        $this->assertCount(3, $this->getMailjetEmailRepository()->findMessages(ReferentMessage::class));
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

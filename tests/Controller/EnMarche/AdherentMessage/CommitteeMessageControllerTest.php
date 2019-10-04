<?php

namespace Tests\AppBundle\Controller\EnMarche\AdherentMessage;

use AppBundle\AdherentMessage\Command\AdherentMessageChangeCommand;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MessengerTestTrait;

/**
 * @group functional
 */
class CommitteeMessageControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

    public function testSupervisorCanCreateMessageAndChangeFilterSuccessfully(): void
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/espace-animateur/en-marche-suisse/messagerie/creer');

        $this->client->submit($crawler->selectButton('Suivant →')->form(['committee_adherent_message' => [
            'label' => 'test',
            'subject' => 'subject',
            'content' => 'message content',
        ]]));

        $this->assertResponseStatusCode(302, $this->client->getResponse());
        $this->assertMessageIsDispatched(AdherentMessageChangeCommand::class);

        $this->client->followRedirect();

        $this->assertResponseStatusCode(200, $this->client->getResponse());
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

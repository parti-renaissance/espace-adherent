<?php

namespace Tests\App\Controller\EnMarche\AdherentMessage;

use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

#[Group('functional')]
class CommitteeMessageControllerTest extends AbstractEnMarcheWebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

    public function testSupervisorCanCreateMessageAndChangeFilterSuccessfully(): void
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/espace-animateur/en-marche-suisse/messagerie/creer');

        $this->client->submit($crawler->selectButton('Suivant →')->form(['adherent_message' => [
            'label' => 'test',
            'subject' => 'subject',
            'content' => 'message content',
        ]]));

        $this->assertResponseStatusCode(302, $this->client->getResponse());
        $this->assertMessageIsDispatched(AdherentMessageChangeCommand::class);

        $this->client->followRedirect();

        $this->assertResponseStatusCode(200, $this->client->getResponse());
    }
}

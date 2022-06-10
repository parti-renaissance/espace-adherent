<?php

namespace Tests\App\Controller\EnMarche;

use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

/**
 * @group functional
 * @group deputy
 */
class DeputyControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

    public function testDeputyCanCreateAdherentMessage(): void
    {
        $this->authenticateAsAdherent($this->client, 'deputy-ch-li@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/espace-depute/messagerie/creer');
        $this->client->submit($crawler->selectButton('Suivant')->form(['adherent_message' => [
            'label' => 'test',
            'subject' => 'subject',
            'content' => 'message content',
        ]]));

        $this->assertTrue($this->client->getResponse()->isRedirection());

        $this->assertMessageIsDispatched(AdherentMessageChangeCommand::class);

        $crawler = $this->client->followRedirect();

        static::assertSame(
            'Filtrer par',
            trim($crawler->filter('.manager__filters__subtitle')->text())
        );
    }
}

<?php

namespace Tests\App\Controller\EnMarche;

use App\AdherentMessage\Command\AdherentMessageChangeCommand;
use PHPUnit\Framework\Attributes\Group;
use Tests\App\AbstractEnMarcheWebTestCase;
use Tests\App\Controller\ControllerTestTrait;
use Tests\App\MessengerTestTrait;

#[Group('functional')]
#[Group('deputy')]
class DeputyControllerTest extends AbstractEnMarcheWebTestCase
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

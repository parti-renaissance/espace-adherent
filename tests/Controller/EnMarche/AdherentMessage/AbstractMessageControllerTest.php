<?php

namespace Tests\AppBundle\Controller\EnMarche\AdherentMessage;

use AppBundle\AdherentMessage\Command\AdherentMessageChangeCommand;
use AppBundle\Entity\AdherentMessage\AbstractAdherentMessage;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\MessengerTestTrait;

/**
 * @group functional
 */
class AbstractMessageControllerTest extends WebTestCase
{
    use ControllerTestTrait;
    use MessengerTestTrait;

    public function testReferentCanCreateMessageAndChangeFilterSuccessfully(): void
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request('GET', '/espace-referent/messagerie/creer');

        $this->client->submit($crawler->selectButton('Suivant →')->form(['adherent_message' => [
            'label' => 'test',
            'subject' => 'subject',
            'content' => 'message content',
        ]]));

        $this->assertResponseStatusCode(302, $this->client->getResponse());
        $this->assertMessageIsDispatched(AdherentMessageChangeCommand::class);

        $crawler = $this->client->followRedirect();

        self::assertSame(
            'Filtrer par zones gérées',
            trim($crawler->filter('h4.manager__filters__subtitle')->text())
        );
    }

    public function testReferentCanVisualizeMessageSuccessfully(): void
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $referentMessage = $this->client->getContainer()->get('doctrine')->getManager()
            ->getRepository(AbstractAdherentMessage::class)
            ->findOneBy(['subject' => 'Synchronized ReferentAdherentMessage message with externalId'])
        ;

        $uuid = $referentMessage->getUuid()->toString();
        $crawler = $this->client->request('GET',
            sprintf('/espace-referent/messagerie/%s/visualiser', $uuid)
        );

        $this->assertResponseStatusCode(200, $this->client->getResponse());

        $buttons = $crawler->filter('div.text--center a.btn--ghosting--blue');

        $this->assertSame(2, $buttons->count());
        $this->assertSame('Modifier les filtres', $buttons->eq(0)->text());
        $this->assertSame('M\'envoyer un e-mail test', $buttons->eq(1)->text());
        $this->assertSame(sprintf('/espace-referent/messagerie/%s/filtrer', $uuid), $buttons->eq(0)->attr('href'));
        $this->assertSame(sprintf('/espace-referent/messagerie/%s/tester', $uuid), $buttons->eq(1)->attr('href'));
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

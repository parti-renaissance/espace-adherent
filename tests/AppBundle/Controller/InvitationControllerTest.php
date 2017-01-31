<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Invite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Repository\InvitationRepository;
use Symfony\Bundle\FrameworkBundle\Client;
use Tests\AppBundle\SqliteWebTestCase;

class InvitationControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /** @var Client */
    private $client;

    /** @var InvitationRepository */
    private $invitationRepository;

    public function testSubscriptionAndRetry()
    {
        // There should not be any invites at the moment
        $this->assertEmpty($this->invitationRepository->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/invitation');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_invitation]')->form([
            'app_invitation[lastName]' => 'Galopin',
            'app_invitation[firstName]' => 'Titouan',
            'app_invitation[email]' => 'titouan.galopin@en-marche.fr',
            'app_invitation[message]' => 'Je t\'invite à rejoindre En Marche !',
        ]));

        // Subscription should have been saved
        $this->assertCount(1, $invites = $this->invitationRepository->findAll());
        $this->assertInstanceOf(Invite::class, $invite = $invites[0]);
        $this->assertSame('titouan.galopin@en-marche.fr', $invite->getEmail());
        $this->assertSame('Titouan', $invite->getFirstName());
        $this->assertSame('Galopin', $invite->getLastName());
        $this->assertSame('Je t\'invite à rejoindre En Marche !', $invite->getMessage());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Try another time with the same email (should fail)
        $crawler = $this->client->request(Request::METHOD_GET, '/invitation');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_invitation]')->form([
            'app_invitation[lastName]' => 'Dupond',
            'app_invitation[firstName]' => 'Jean',
            'app_invitation[email]' => 'titouan.galopin@en-marche.fr',
            'app_invitation[message]' => 'Je t\'invite à rejoindre En Marche !',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Invitation should not have been saved
        $this->assertCount(1, $this->invitationRepository->findAll());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures([]);

        $this->client = $this->makeClient();
        $this->container = $this->client->getContainer();
        $this->invitationRepository = $this->getInvitationRepository();
    }

    protected function tearDown()
    {
        $this->loadFixtures([]);

        $this->invitationRepository = null;
        $this->container = null;
        $this->client = null;

        parent::tearDown();
    }
}

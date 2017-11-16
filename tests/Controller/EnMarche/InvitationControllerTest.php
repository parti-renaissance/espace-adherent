<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\DataFixtures\ORM\LoadHomeBlockData;
use AppBundle\Entity\Invite;
use AppBundle\Mailer\Message\InvitationMessage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Repository\InvitationRepository;
use Symfony\Bundle\FrameworkBundle\Client;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

/**
 * @group functional
 * @group invitation
 */
class InvitationControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    /** @var Client */
    private $client;

    /** @var InvitationRepository */
    private $invitationRepository;

    public function testInvite()
    {
        // There should not be any invites at the moment
        $this->assertEmpty($this->invitationRepository->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/invitation');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_invitation]')->form([
            'g-recaptcha-response' => 'dummy',
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

        // Email should have been sent
        $this->assertCount(1, $this->getEmailRepository()->findMessages(InvitationMessage::class));

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

        $this->init([
            LoadHomeBlockData::class,
        ]);

        $this->invitationRepository = $this->getInvitationRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->invitationRepository = null;

        parent::tearDown();
    }
}

<?php

namespace Tests\AppBundle\Controller\EnMarche;

use AppBundle\Entity\Invite;
use AppBundle\Mailer\Message\MovementInvitationMessage;
use AppBundle\Repository\InvitationRepository;
use Liip\FunctionalTestBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group invitation
 */
class InvitationControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    /** @var InvitationRepository */
    private $invitationRepository;

    public function testInvite()
    {
        // There should not be any invites at the moment
        $this->assertEmpty($this->invitationRepository->findAll());

        // Initial form
        $crawler = $this->client->request(Request::METHOD_GET, '/invitation');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $crawler = $this->client->submit($crawler->filter('form[name=app_invitation]')->form([
            'g-recaptcha-response' => 'dummy',
            'app_invitation[lastName]' => 'Galopin',
            'app_invitation[firstName]' => 'Titouan',
            'app_invitation[email]' => 'titouan.galopin@en-marche.fr',
            'app_invitation[message]' => 'Je t\'invite à rejoindre En Marche !',
        ]));

        $this->isSuccessful($this->client->getResponse());
        $this->assertCount(1, $errors = $crawler->filter('.form__errors'));
        $this->assertSame('L\'acceptation des mentions d\'information est obligatoire pour donner suite à votre demande.', $errors->eq(0)->text());

        $this->client->submit($crawler->filter('form[name=app_invitation]')->form([
            'g-recaptcha-response' => 'dummy',
            'app_invitation[lastName]' => 'Galopin',
            'app_invitation[firstName]' => 'Titouan',
            'app_invitation[email]' => 'titouan.galopin@en-marche.fr',
            'app_invitation[message]' => 'Je t\'invite à rejoindre En Marche !',
            'app_invitation[personalDataCollection]' => true,
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
        $this->assertCount(1, $this->getEmailRepository()->findMessages(MovementInvitationMessage::class));

        // Try another time with the same email (should fail)
        $crawler = $this->client->request(Request::METHOD_GET, '/invitation');

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name=app_invitation]')->form([
            'app_invitation[lastName]' => 'Dupond',
            'app_invitation[firstName]' => 'Jean',
            'app_invitation[email]' => 'titouan.galopin@en-marche.fr',
            'app_invitation[message]' => 'Je t\'invite à rejoindre En Marche !',
            'app_invitation[personalDataCollection]' => true,
        ]));

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        // Invitation should not have been saved
        $this->assertCount(1, $this->invitationRepository->findAll());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init();

        $this->invitationRepository = $this->getInvitationRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->invitationRepository = null;

        parent::tearDown();
    }
}

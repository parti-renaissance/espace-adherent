<?php

namespace Tests\AppBundle\Controller;

use AppBundle\Entity\Invite;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InvitationControllerTest extends WebTestCase
{
    public function testSubscriptionAndRetry()
    {
        $client = static::createClient();

        $entityManager = $client->getContainer()->get('doctrine.orm.entity_manager');
        $invitesRepository = $entityManager->getRepository('AppBundle:Invite');

        // There should not be any invite for the moment
        $this->assertEmpty($invitesRepository->findAll());

        // Initial form
        $crawler = $client->request(Request::METHOD_GET, '/invitation');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=app_invitation]')->form([
            'app_invitation[lastName]' => 'Galopin',
            'app_invitation[firstName]' => 'Titouan',
            'app_invitation[email]' => 'titouan.galopin@en-marche.fr',
            'app_invitation[message]' => 'Je t\'invite à rejoindre En Marche !',
        ]);

        $client->submit($form);

        // Subscription should have been saved
        $invites = $invitesRepository->findAll();
        $this->assertCount(1, $invites);

        /** @var Invite $invite */
        $invite = $invites[0];

        $this->assertEquals('titouan.galopin@en-marche.fr', $invite->getEmail());
        $this->assertEquals('Titouan', $invite->getFirstName());
        $this->assertEquals('Galopin', $invite->getLastName());
        $this->assertEquals('Je t\'invite à rejoindre En Marche !', $invite->getMessage());

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Try another time with the same email (should fail)
        $crawler = $client->request(Request::METHOD_GET, '/invitation');
        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        $form = $crawler->filter('form[name=app_invitation]')->form([
            'app_invitation[lastName]' => 'Dupond',
            'app_invitation[firstName]' => 'Jean',
            'app_invitation[email]' => 'titouan.galopin@en-marche.fr',
            'app_invitation[message]' => 'Je t\'invite à rejoindre En Marche !',
        ]);

        $client->submit($form);

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());

        // Invitation should not have been saved
        $this->assertCount(1, $invitesRepository->findAll());
    }
}

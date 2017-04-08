<?php

namespace AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadTonMacronData;
use AppBundle\Repository\MailjetEmailRepository;
use AppBundle\Repository\TonMacronChoiceRepository;
use AppBundle\Repository\TonMacronFriendInvitationRepository;
use AppBundle\TonMacron\InvitationProcessor;
use AppBundle\TonMacron\InvitationProcessorHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

class TonMacronControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    const INVITATION_PATH = '/ton-macron/invitation';
    const INVITATION_RESTART_PATH = '/ton-macron/invitation/recommencer';
    const INVITATION_SENT_PATH = '/ton-macron/invitation/merci';

    /* @var TonMacronChoiceRepository */
    private $tonMacronChoiceRepository;

    /* @var TonMacronFriendInvitationRepository */
    private $tonMacronInvitationRepository;

    /* @var MailjetEmailRepository */
    private $emailRepository;

    /**
     * @group functionnal
     */
    public function testInviteAction()
    {
        $invitation = new InvitationProcessor();

        $crawler = $this->client->request(Request::METHOD_GET, self::INVITATION_PATH);

        $this->assertEquals($invitation, $this->getCurrentInvitation());
        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="ton_macron_invitation[fill_info]"]'));

        $this->client->submit($crawler->filter('form[name="ton_macron_invitation"]')->form([
            'ton_macron_invitation[friendFirstName]' => $invitation->friendFirstName = 'Béatrice',
            'ton_macron_invitation[friendAge]' => '32',
            'ton_macron_invitation[friendGender]' => $invitation->friendGender = 'female',
            'ton_macron_invitation[friendPosition]' => '4',
        ]));

        $invitation->friendAge = 32;
        $invitation->friendPosition = $this->getChoices(4);
        $invitation->marking = InvitationProcessor::STATE_NEEDS_FRIEND_PROJECT;

        //$this->assertEquals($invitation, $this->getCurrentInvitation());
        $this->assertClientIsRedirectedTo(self::INVITATION_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="ton_macron_invitation[fill_project]"]'));

        $this->client->submit($crawler->filter('form[name="ton_macron_invitation"]')->form([
            'ton_macron_invitation[friendProject]' => '19',
        ]));

        $invitation->friendPosition = $this->getChoices(19);
        $invitation->marking = InvitationProcessor::STATE_NEEDS_FRIEND_INTERESTS;

        $this->assertEquals($invitation, $this->getCurrentInvitation());
        $this->assertClientIsRedirectedTo(self::INVITATION_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="ton_macron_invitation[fill_interests]"]'));

        $this->client->submit($crawler->filter('form[name="ton_macron_invitation"]')->form([
            'ton_macron_invitation[friendInterests]' => ['28', '46'],
        ]));

        $invitation->friendPosition = $this->getChoices([28, 46]);
        $invitation->marking = InvitationProcessor::STATE_NEEDS_SELF_REASONS;

        $this->assertEquals($invitation, $this->getCurrentInvitation());
        $this->assertClientIsRedirectedTo(self::INVITATION_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="ton_macron_invitation[fill_reasons]"]'));

        $this->client->submit($crawler->filter('form[name="ton_macron_invitation"]')->form([
            'ton_macron_invitation[selfReasons]' => ['61', '62'],
        ]));

        $invitation->friendPosition = $this->getChoices([61, 62]);
        $invitation->marking = InvitationProcessor::STATE_SUMMARY;

        $currentInvitation = $this->getCurrentInvitation();

        $this->assertEquals($invitation->selfReasons, $currentInvitation->selfReasons);
        $this->assertNotEquals($invitation->messageContent, $currentInvitation->messageContent);
        $this->assertContains('S01C04', $currentInvitation->messageContent);
        $this->assertContains('S02C19', $currentInvitation->messageContent);
        $this->assertContains('S03C28', $currentInvitation->messageContent);
        $this->assertContains('S03C46', $currentInvitation->messageContent);
        $this->assertContains('S04C61', $currentInvitation->messageContent);
        $this->assertContains('S04C62', $currentInvitation->messageContent);
        $this->assertClientIsRedirectedTo(self::INVITATION_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="ton_macron_invitation[send]"]'));

        $this->client->submit($crawler->filter('form[name="ton_macron_invitation"]')->form([
            'ton_macron_invitation[messageSubject]' => $invitation->messageSubject = 'Toujours envie de voter blanc ?',
            'ton_macron_invitation[selfFirstName]' => $invitation->selfFirstName = 'Marie',
            'ton_macron_invitation[selfLastName]' => $invitation->selfLastName = 'Dupont',
            'ton_macron_invitation[selfEmail]' => $invitation->selfEmail = 'marie.dupont@domain.tld',
            'ton_macron_invitation[frienEmail]' => $invitation->friendEmail = 'beatrice@gmail.tld',
        ]));

        $invitation->marking = InvitationProcessor::STATE_SENT;

        $this->assertEquals($invitation, $this->getCurrentInvitation());
        $this->assertClientIsRedirectedTo(self::INVITATION_SENT_PATH, $this->client);
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadTonMacronData::class,
        ]);

        $this->tonMacronChoiceRepository = $this->getTonMacronInvitationRepository();
        $this->tonMacronInvitationRepository = $this->getTonMacronInvitationRepository();
        $this->emailRepository = $this->getMailjetEmailRepository();
    }

    protected function tearDown()
    {
        $this->kill();

        $this->emailRepository = null;
        $this->tonMacronInvitationRepository = null;
        $this->tonMacronChoiceRepository = null;

        parent::tearDown();
    }

    private function getTonMacronInvitationHandler(): InvitationProcessorHandler
    {
        return $this->container->get('app.ton_macron.invitation_processor_handler');
    }

    private function getCurrentInvitation()
    {
        return $this->getTonMacronInvitationHandler()->start($this->client->getRequest()->getSession());
    }

    /**
     * @var string|int[] $ids
     */
    private function getChoices($ids)
    {
        return $this->tonMacronChoiceRepository->findBy(['id' => (array) $ids]);
    }
}

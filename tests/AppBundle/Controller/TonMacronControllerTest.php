<?php

namespace AppBundle\Controller;

use AppBundle\DataFixtures\ORM\LoadTonMacronData;
use AppBundle\Entity\MailjetEmail;
use AppBundle\Entity\TonMacronChoice;
use AppBundle\Repository\MailjetEmailRepository;
use AppBundle\Repository\TonMacronChoiceRepository;
use AppBundle\Repository\TonMacronFriendInvitationRepository;
use AppBundle\TonMacron\InvitationProcessor;
use AppBundle\TonMacron\InvitationProcessorHandler;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\AppBundle\Controller\ControllerTestTrait;
use Tests\AppBundle\SqliteWebTestCase;

class TonMacronControllerTest extends SqliteWebTestCase
{
    use ControllerTestTrait;

    const INVITATION_PATH = '/pourquoichoisirmacron';
    const INVITATION_RESTART_PATH = '/pourquoichoisirmacron/recommencer';
    const INVITATION_SENT_PATH = '/pourquoichoisirmacron/merci';

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
        $this->assertCount(0, $this->emailRepository->findAll());

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
        $invitation->friendPosition = $this->getChoice(4);
        $invitation->marking = InvitationProcessor::STATE_NEEDS_FRIEND_PROJECT;

        $this->assertEquals($invitation, $this->getCurrentInvitation());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::INVITATION_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="ton_macron_invitation[fill_project]"]'));

        $this->client->submit($crawler->filter('form[name="ton_macron_invitation"]')->form([
            'ton_macron_invitation[friendProject]' => '19',
        ]));

        $invitation->friendProject = $this->getChoice(19);
        $invitation->marking = InvitationProcessor::STATE_NEEDS_FRIEND_INTERESTS;

        $this->assertEquals($invitation, $this->getCurrentInvitation());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::INVITATION_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->selectButton('ton_macron_invitation[fill_interests]')->form([
            'ton_macron_invitation[friendInterests]' => [
                0 => 28,
                18 => 46,
            ],
        ]));

        $invitation->friendInterests = $this->getChoices([28, 46]);
        $invitation->marking = InvitationProcessor::STATE_NEEDS_SELF_REASONS;

        $this->assertEquals($invitation, $this->getCurrentInvitation());
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::INVITATION_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="ton_macron_invitation[fill_reasons]"]'));

        $this->client->submit($crawler->filter('form[name="ton_macron_invitation"]')->form([
            'ton_macron_invitation[selfReasons]' => [1 => '61', 2 => '62'],
        ]));

        $invitation->selfReasons = $this->getChoices([61, 62]);
        $invitation->marking = InvitationProcessor::STATE_SUMMARY;

        $currentInvitation = $this->getCurrentInvitation();

        $this->assertEquals($invitation->selfReasons, $currentInvitation->selfReasons);
        $this->assertNotEquals($invitation->messageContent, $currentInvitation->messageContent);
        foreach ($invitation->getArguments() as $choice) {
            $this->assertContains($choice, $currentInvitation->messageContent);
        }
        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::INVITATION_PATH, $this->client);

        $crawler = $this->client->followRedirect();

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());
        $this->assertCount(1, $crawler->filter('button[name="ton_macron_invitation[send]"]'));

        $this->client->submit($crawler->filter('form[name="ton_macron_invitation"]')->form([
            'ton_macron_invitation[messageSubject]' => $invitation->messageSubject = 'Toujours envie de voter blanc ?',
            'ton_macron_invitation[selfFirstName]' => $invitation->selfFirstName = 'Marie',
            'ton_macron_invitation[selfLastName]' => $invitation->selfLastName = 'Dupont',
            'ton_macron_invitation[selfEmail]' => $invitation->selfEmail = 'marie.dupont@example.org',
            'ton_macron_invitation[friendEmail]' => $invitation->friendEmail = 'beatrice@example.org',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::INVITATION_SENT_PATH, $this->client);
        $this->assertNull($this->client->getRequest()->getSession()->get(InvitationProcessorHandler::SESSION_KEY));
        $this->assertCount(1, $mails = $this->emailRepository->findAll());

        /** @var MailjetEmail $mail */
        $mail = $mails[0];

        $this->assertSame('TonMacronFriendMessage', $mail->getMessageClass());
        $this->assertContains('beatrice@example.org', $mail->getRecipientsAsString());
    }

    public function testRestartAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, self::INVITATION_PATH);

        $this->assertResponseStatusCode(Response::HTTP_OK, $this->client->getResponse());

        $this->client->submit($crawler->filter('form[name="ton_macron_invitation"]')->form([
            'ton_macron_invitation[friendFirstName]' => 'Béatrice',
            'ton_macron_invitation[friendAge]' => '32',
            'ton_macron_invitation[friendGender]' => 'female',
            'ton_macron_invitation[friendPosition]' => '4',
        ]));

        $this->assertResponseStatusCode(Response::HTTP_FOUND, $this->client->getResponse());
        $this->assertClientIsRedirectedTo(self::INVITATION_PATH, $this->client);

        $this->client->followRedirect();
        $this->client->request(Request::METHOD_GET, self::INVITATION_RESTART_PATH);

        $this->assertNull($this->client->getRequest()->getSession()->get(InvitationProcessorHandler::SESSION_KEY));
    }

    protected function setUp()
    {
        parent::setUp();

        $this->init([
            LoadTonMacronData::class,
        ]);

        $this->tonMacronChoiceRepository = $this->getTonMacronChoiceRepository();
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

    private function getChoice(int $id): ?TonMacronChoice
    {
        return $this->tonMacronChoiceRepository->find($id);
    }

    /**
     * @param int[] $ids
     *
     * @return TonMacronChoice[]|ArrayCollection
     */
    private function getChoices(array $ids): ArrayCollection
    {
        return new ArrayCollection($this->tonMacronChoiceRepository->findBy(['id' => $ids]));
    }
}

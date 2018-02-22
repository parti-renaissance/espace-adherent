<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\TonMacronFriendInvitation;
use AppBundle\Mailer\Message\TonMacronFriendMessage;

/**
 * @group message
 */
class TonMacronFriendMessageTest extends MessageTestCase
{
    /**
     * @var TonMacronFriendInvitation|null
     */
    private $tonMacronFriendInvitation;

    public function testCreateFromInvitation(): void
    {
        $message = TonMacronFriendMessage::create($this->tonMacronFriendInvitation);

        self::assertMessage(
            TonMacronFriendMessage::class,
            [
                'message' => 'Contenu du message de test.',
            ],
            $message
        );

        self::assertSender('Bernard Smith', null, $message);
        self::assertReplyTo('bernard@example.com', $message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'jean@example.com',
            null,
            [
                'message' => 'Contenu du message de test.',
            ],
            $message
        );

        self::assertCountCC(1, $message);
        self::assertMessageCC('bernard@example.com', $message);
    }

    protected function setUp()
    {
        $this->tonMacronFriendInvitation = $this->createMock(TonMacronFriendInvitation::class);

        $this->tonMacronFriendInvitation
            ->expects(self::once())
            ->method('getFriendEmailAddress')
            ->willReturn('jean@example.com')
        ;
        $this->tonMacronFriendInvitation
            ->expects(self::exactly(2))
            ->method('getAuthorEmailAddress')
            ->willReturn('bernard@example.com')
        ;
        $this->tonMacronFriendInvitation
            ->expects(self::once())
            ->method('getAuthorFirstName')
            ->willReturn('Bernard')
        ;
        $this->tonMacronFriendInvitation
            ->expects(self::once())
            ->method('getAuthorLastName')
            ->willReturn('Smith')
        ;
        $this->tonMacronFriendInvitation
            ->expects(self::once())
            ->method('getMailBody')
            ->willReturn('Contenu du message de test.')
        ;
    }

    protected function tearDown()
    {
        $this->tonMacronFriendInvitation = null;
    }
}

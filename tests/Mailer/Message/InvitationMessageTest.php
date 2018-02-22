<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\Invite;
use AppBundle\Mailer\Message\InvitationMessage;
use Ramsey\Uuid\Uuid;

/**
 * @group message
 */
class InvitationMessageTest extends MessageTestCase
{
    /**
     * @var Invite|null
     */
    private $invitation;

    public function testCreateFromInvite(): void
    {
        $message = InvitationMessage::create($this->invitation);

        self::assertMessage(
            InvitationMessage::class,
            [
                'sender_firstname' => 'Jean',
                'sender_lastname' => 'Bernard',
                'message' => 'Bonjour, ici Jean.',
            ],
            $message
        );

        self::assertNoSender($message);
        self::assertNoReplyTo($message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'recipient@example.com',
            null,
            [
                'sender_firstname' => 'Jean',
                'sender_lastname' => 'Bernard',
                'message' => 'Bonjour, ici Jean.',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->invitation = $this->createMock(Invite::class);

        $this->invitation
            ->expects(self::once())
            ->method('getUuid')
            ->willReturn(Uuid::uuid4())
        ;
        $this->invitation
            ->expects(self::once())
            ->method('getEmail')
            ->willReturn('recipient@example.com')
        ;
        $this->invitation
            ->expects(self::once())
            ->method('getFirstName')
            ->willReturn('Jean')
        ;
        $this->invitation
            ->expects(self::once())
            ->method('getLastName')
            ->willReturn('Bernard')
        ;
        $this->invitation
            ->expects(self::once())
            ->method('getMessage')
            ->willReturn('Bonjour, ici Jean.')
        ;
    }

    protected function tearDown()
    {
        $this->invitation = null;
    }
}

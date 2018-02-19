<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Entity\NewsletterInvite;
use AppBundle\Mailer\Message\NewsletterInvitationMessage;

/**
 * @group message
 */
class NewsletterInvitationMessageTest extends MessageTestCase
{
    /**
     * @var NewsletterInvite|null
     */
    private $invitation;

    public function testCreate(): void
    {
        $message = NewsletterInvitationMessage::create(
            $this->invitation,
            'https://enmarche.code/newsletter/subscribe'
        );

        self::assertMessage(
            NewsletterInvitationMessage::class,
            [
                'sender_first_name' => 'Jean',
                'subscribe_link' => 'https://enmarche.code/newsletter/subscribe',
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
                'sender_first_name' => 'Jean',
                'subscribe_link' => 'https://enmarche.code/newsletter/subscribe',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->invitation = $this->createMock(NewsletterInvite::class);

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
    }

    protected function tearDown()
    {
        $this->invitation = null;
    }
}

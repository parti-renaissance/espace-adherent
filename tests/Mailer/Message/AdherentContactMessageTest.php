<?php

namespace Tests\AppBundle\Mailer\Message;

use AppBundle\Contact\ContactMessage;
use AppBundle\Mailer\Message\AdherentContactMessage;

/**
 * @group message
 */
class AdherentContactMessageTest extends MessageTestCase
{
    /**
     * @var ContactMessage|null
     */
    private $contactMessage;

    public function testCreate(): void
    {
        $message = AdherentContactMessage::create($this->contactMessage);

        self::assertMessage(
            AdherentContactMessage::class,
            [
                'recipient_first_name' => 'Bernard',
                'sender_first_name' => 'Jean',
                'message' => 'Bonsoir Bernard, ici Jean.',
            ],
            $message
        );

        self::assertNoSender($message);
        self::assertReplyTo('jean@example.com', $message);

        self::assertCountRecipients(1, $message);

        self::assertMessageRecipient(
            'bernard@example.com',
            'Bernard Smith',
            [
                'recipient_first_name' => 'Bernard',
                'sender_first_name' => 'Jean',
                'message' => 'Bonsoir Bernard, ici Jean.',
            ],
            $message
        );

        self::assertNoCC($message);
    }

    protected function setUp()
    {
        $this->contactMessage = $this->createMock(ContactMessage::class);

        $this->contactMessage
            ->expects(self::once())
            ->method('getFrom')
            ->willReturn($this->createAdherent('jean@example.com', 'Jean', 'Doe'))
        ;
        $this->contactMessage
            ->expects(self::once())
            ->method('getTo')
            ->willReturn($this->createAdherent('bernard@example.com', 'Bernard', 'Smith'))
        ;
        $this->contactMessage
            ->expects(self::once())
            ->method('getContent')
            ->willReturn('Bonsoir Bernard, ici Jean.')
        ;
    }

    protected function tearDown()
    {
        $this->contactMessage = null;
    }
}
